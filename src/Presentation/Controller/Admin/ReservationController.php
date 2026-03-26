<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Reservation\CancelReservationUseCase;
use Rore\Application\Reservation\ConfirmReservationUseCase;
use Rore\Application\Reservation\GetCalendarDataUseCase;
use Rore\Application\Reservation\GetReservationDetailsUseCase;
use Rore\Application\Reservation\GetReservationsUseCase;
use Rore\Application\Reservation\SetReservationStatusUseCase;
use Rore\Domain\Shared\ValueObject\DateRange;
use Rore\Framework\Http\Route;

class ReservationController extends AdminController
{
    public function __construct(
        private readonly GetReservationsUseCase       $getReservationsUseCase,
        private readonly GetReservationDetailsUseCase $getReservationDetailsUseCase,
        private readonly GetCalendarDataUseCase       $getCalendarDataUseCase,
        private readonly SetReservationStatusUseCase  $setReservationStatusUseCase,
        private readonly ConfirmReservationUseCase    $confirmReservationUseCase,
        private readonly CancelReservationUseCase     $cancelReservationUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin/reservations')]
    public function index(): void
    {
        $status       = $this->request->queryString->getString('status', 'all');
        $reservations = $status === 'all'
            ? $this->getReservationsUseCase->all()
            : $this->getReservationsUseCase->byStatus($status);

        $this->render('admin/reservations/list', [
            'title'         => 'Réservations',
            'reservations'  => $reservations,
            'currentStatus' => $status,
        ]);
    }

    #[Route('GET', '/admin/reservations/calendrier')]
    public function calendar(): void
    {
        $month = $this->request->queryString->getInt('month', (int) date('n'));
        $year  = $this->request->queryString->getInt('year', (int) date('Y'));

        $data = $this->getCalendarDataUseCase->execute($month, $year);

        $this->render('admin/reservations/calendar', [
            'title'        => 'Calendrier',
            'reservations' => $data['reservations'],
            'products'     => $data['products'],
            'month'        => $data['month'],
            'year'         => $data['year'],
            'start'        => $data['start'],
            'end'          => $data['end'],
        ]);
    }

    #[Route('GET', '/admin/reservations/{id}')]
    public function show(string $id): void
    {
        try {
            $data = $this->getReservationDetailsUseCase->execute((int) $id);
        } catch (\Throwable) {
            $this->redirect($this->urlResolver->resolve(self::class . '.index'));
        }

        $this->render('admin/reservations/show', [
            'title'                => 'Réservation #' . $id,
            'reservation'          => $data['reservation'],
            'products'             => $data['products'],
            'packs'                => $data['packs'],
            'productCurrentPrices' => $data['productCurrentPrices'],
            'dateRange'            => new DateRange(
                $data['reservation']->getStartDate(),
                $data['reservation']->getEndDate(),
            ),
        ]);
    }

    #[Route('POST', '/admin/reservations/{id}/devis')]
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

    #[Route('POST', '/admin/reservations/{id}/statut')]
    public function setStatus(string $id): void
    {
        $this->requirePost();
        $newStatus = $this->request->body->getString('status');
        try {
            $this->setReservationStatusUseCase->execute((int) $id, $newStatus);
            $this->flash('success', 'Statut mis à jour.');
        } catch (\Throwable $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->urlResolver->resolve(self::class . '.show', ['id' => $id]));
    }

    #[Route('POST', '/admin/reservations/{id}/confirmer')]
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

    #[Route('POST', '/admin/reservations/{id}/annuler')]
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
}
