<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Reservation\CancelReservationUseCase;
use Rore\Application\Reservation\ConfirmReservationUseCase;
use Rore\Application\Reservation\GetReservationsUseCase;
use Rore\Application\Reservation\SetReservationStatusUseCase;
use Rore\Domain\Shared\ValueObject\DateRange;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;
use Rore\Domain\Catalog\Service\PricingService;

class ReservationController extends AdminController
{
    public function __construct(
        private readonly MySqlReservationRepository $repo,
        private readonly MySqlProductRepository     $productRepo,
        private readonly MySqlPackRepository        $packRepo,
        private readonly PricingService          $pricing,
        private readonly GetReservationsUseCase     $getReservationsUseCase,
        private readonly SetReservationStatusUseCase $setReservationStatusUseCase,
        private readonly ConfirmReservationUseCase  $confirmReservationUseCase,
        private readonly CancelReservationUseCase   $cancelReservationUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    public function index(): void
    {
        $status       = $this->request->queryString->getStringParam('status', 'all');
        $reservations = $status === 'all'
            ? $this->getReservationsUseCase->all()
            : $this->getReservationsUseCase->byStatus($status);

        $this->render('admin/reservations/list', [
            'title'        => 'Réservations',
            'reservations' => $reservations,
            'currentStatus' => $status,
        ]);
    }

    public function show(string $id): void
    {
        $reservation = $this->repo->findById((int) $id);
        if (!$reservation) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }

        // Charger les noms produits
        $products             = [];
        $packs                = [];
        $productCurrentPrices = [];
        foreach ($reservation->getItems() as $item) {
            if ($item->getPackId() !== null) {
                $pack = $this->packRepo->findById($item->getPackId());
                if ($pack) $packs[$pack->getId()] = $pack;
            } else {
                $product = $this->productRepo->findById($item->getProductId());
                $products[$item->getProductId()] = $product;
                if ($product) {
                    $productCurrentPrices[$item->getProductId()] = $this->pricing->calculate(
                        $product,
                        $reservation->getStartDate(),
                        $reservation->getEndDate(),
                    );
                }
            }
        }

        $this->render('admin/reservations/show', [
            'title'                => 'Réservation #' . $id,
            'reservation'          => $reservation,
            'products'             => $products,
            'packs'                => $packs,
            'productCurrentPrices' => $productCurrentPrices,
            'dateRange'            => new DateRange($reservation->getStartDate(), $reservation->getEndDate()),
        ]);
    }

    public function quote(string $id): void
    {
        $this->requirePost();
        try {
            $this->setReservationStatusUseCase->execute((int) $id, 'quoted');
            $this->flash('success', 'Devis marqué comme envoyé.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.show', ['id' => $id]));
    }

    public function setStatus(string $id): void
    {
        $this->requirePost();
        $newStatus = $this->request->body->getStringParam('status');
        try {
            $this->setReservationStatusUseCase->execute((int) $id, $newStatus);
            $this->flash('success', 'Statut mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.show', ['id' => $id]));
    }

    public function confirm(string $id): void
    {
        $this->requirePost();
        try {
            $this->confirmReservationUseCase->execute((int) $id);
            $this->flash('success', 'Réservation confirmée.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.show', ['id' => $id]));
    }

    public function cancel(string $id): void
    {
        $this->requirePost();
        try {
            $this->cancelReservationUseCase->execute((int) $id);
            $this->flash('success', 'Réservation annulée.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.show', ['id' => $id]));
    }

    public function calendar(): void
    {
        $month = $this->request->queryString->getIntParam('month', (int) date('n'));
        $year  = $this->request->queryString->getIntParam('year', (int) date('Y'));

        $start = new \DateTimeImmutable("$year-$month-01");
        $end   = $start->modify('last day of this month');

        $reservations = $this->repo->findConfirmedOverlapping($start, $end);

        // Charger les produits référencés dans les réservations (pour indicateur on-demand)
        $products = [];
        foreach ($reservations as $r) {
            foreach ($r->getItems() as $item) {
                $pid = $item->getProductId();
                if (!isset($products[$pid])) {
                    $products[$pid] = $this->productRepo->findById($pid);
                }
            }
        }

        $this->render('admin/reservations/calendar', [
            'title'        => 'Calendrier',
            'reservations' => $reservations,
            'products'     => $products,
            'month'        => $month,
            'year'         => $year,
            'start'        => $start,
            'end'          => $end,
        ]);
    }
}
