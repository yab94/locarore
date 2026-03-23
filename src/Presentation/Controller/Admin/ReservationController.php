<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Reservation\CancelReservationUseCase;
use Rore\Application\Reservation\ConfirmReservationUseCase;
use Rore\Application\Reservation\GetReservationsUseCase;
use Rore\Application\Reservation\SetReservationStatusUseCase;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Security\CsrfTokenManager;
use Rore\Infrastructure\Persistence\MySqlProductRepository;
use Rore\Infrastructure\Persistence\MySqlReservationRepository;

class ReservationController extends AdminController
{
    public function __construct(
        private readonly MySqlReservationRepository $repo,
        private readonly MySqlProductRepository     $productRepo,
        private readonly GetReservationsUseCase     $getReservationsUseCase,
        private readonly SetReservationStatusUseCase $setReservationStatusUseCase,
        private readonly ConfirmReservationUseCase  $confirmReservationUseCase,
        private readonly CancelReservationUseCase   $cancelReservationUseCase,
        SessionStorageInterface                     $session,
        CsrfTokenManager                            $csrfTokenManager,
    ) {
        parent::__construct($session, $csrfTokenManager);
    }

    public function index(): void
    {
        $status       = $this->inputString('status', 'all');
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
            $this->redirect('/admin/reservations');
        }

        // Charger les noms produits
        $products = [];
        foreach ($reservation->getItems() as $item) {
            $products[$item->getProductId()] = $this->productRepo->findById($item->getProductId());
        }

        $this->render('admin/reservations/show', [
            'title'       => 'Réservation #' . $id,
            'reservation' => $reservation,
            'products'    => $products,
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
        $this->redirect('/admin/reservations/' . $id);
    }

    public function setStatus(string $id): void
    {
        $this->requirePost();
        $newStatus = $this->inputString('status');
        try {
            $this->setReservationStatusUseCase->execute((int) $id, $newStatus);
            $this->flash('success', 'Statut mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect('/admin/reservations/' . $id);
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
        $this->redirect('/admin/reservations/' . $id);
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
        $this->redirect('/admin/reservations/' . $id);
    }

    public function calendar(): void
    {
        $month = $this->inputInt('month', (int) date('n'));
        $year  = $this->inputInt('year', (int) date('Y'));

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
