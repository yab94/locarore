<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Framework\Database;
use Rore\Domain\Reservation\Entity\Reservation;
use Rore\Domain\Reservation\Entity\ReservationItem;
use Rore\Domain\Reservation\Repository\ReservationRepositoryInterface;

class MySqlReservationRepository implements ReservationRepositoryInterface
{


    public function __construct(private readonly Database $connection)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->connection->query('SELECT * FROM reservations ORDER BY created_at DESC');
        $reservations = array_map([$this, 'hydrate'], $stmt->fetchAll());
        return $this->loadItems($reservations);
    }

    public function findById(int $id): ?Reservation
    {
        $stmt = $this->connection->prepare('SELECT * FROM reservations WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $reservation = $this->hydrate($row);
        $reservation->setItems($this->fetchItems($id));
        return $reservation;
    }

    public function findByStatus(string $status): array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM reservations WHERE status = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$status]);
        $reservations = array_map([$this, 'hydrate'], $stmt->fetchAll());
        return $this->loadItems($reservations);
    }

    public function findConfirmedOverlapping(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): array {
        $stmt = $this->connection->prepare(
            "SELECT * FROM reservations
              WHERE status IN ('confirmed','quoted')
                AND start_date <= ?
                AND end_date   >= ?"
        );
        $stmt->execute([
            $end->format('Y-m-d'),
            $start->format('Y-m-d'),
        ]);
        $reservations = array_map([$this, 'hydrate'], $stmt->fetchAll());
        return $this->loadItems($reservations);
    }

    public function save(Reservation $reservation): int
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO reservations
                (customer_name, customer_email, customer_phone, customer_address, event_address,
                 start_date, end_date, status, notes, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $reservation->getCustomerName(),
            $reservation->getCustomerEmail(),
            $reservation->getCustomerPhone(),
            $reservation->getCustomerAddress(),
            $reservation->getEventAddress(),
            $reservation->getStartDate()->format('Y-m-d'),
            $reservation->getEndDate()->format('Y-m-d'),
            $reservation->getStatus(),
            $reservation->getNotes(),
            $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
            $reservation->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->connection->lastInsertId();

        foreach ($reservation->getItems() as $item) {
            $this->insertItem($id, $item);
        }

        return $id;
    }

    public function update(Reservation $reservation): void
    {
        $stmt = $this->connection->prepare(
            'UPDATE reservations
                SET status = ?, notes = ?, updated_at = ?
              WHERE id = ?'
        );
        $stmt->execute([
            $reservation->getStatus(),
            $reservation->getNotes(),
            $reservation->getUpdatedAt()->format('Y-m-d H:i:s'),
            $reservation->getId(),
        ]);
    }

    // --- Private helpers ------------------------------------------------

    private function insertItem(int $reservationId, ReservationItem $item): void
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO reservation_items (reservation_id, product_id, pack_id, quantity, unit_price_snapshot)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $reservationId,
            $item->getProductId(),
            $item->getPackId(),
            $item->getQuantity(),
            $item->getUnitPriceSnapshot(),
        ]);
    }

    /** @param Reservation[] $reservations */
    private function loadItems(array $reservations): array
    {
        if (empty($reservations)) return $reservations;

        $ids = implode(',', array_map(fn($r) => $r->getId(), $reservations));
        $stmt = $this->connection->query(
            "SELECT * FROM reservation_items WHERE reservation_id IN ($ids)"
        );

        $itemsByReservation = [];
        foreach ($stmt->fetchAll() as $row) {
            $itemsByReservation[(int) $row['reservation_id']][] = $this->hydrateItem($row);
        }

        foreach ($reservations as $r) {
            $r->setItems($itemsByReservation[$r->getId()] ?? []);
        }

        return $reservations;
    }

    private function fetchItems(int $reservationId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM reservation_items WHERE reservation_id = ?'
        );
        $stmt->execute([$reservationId]);
        return array_map([$this, 'hydrateItem'], $stmt->fetchAll());
    }

    private function hydrate(array $row): Reservation
    {
        return new Reservation(
            id:              (int) $row['id'],
            customerName:    $row['customer_name'],
            customerEmail:   $row['customer_email'],
            customerPhone:   $row['customer_phone'],
            customerAddress: $row['customer_address'] ?? null,
            eventAddress:    $row['event_address'] ?? null,
            startDate:       new \DateTimeImmutable($row['start_date']),
            endDate:         new \DateTimeImmutable($row['end_date']),
            status:          $row['status'],
            notes:           $row['notes'],
            createdAt:       new \DateTimeImmutable($row['created_at']),
            updatedAt:       new \DateTimeImmutable($row['updated_at']),
        );
    }

    /**
     * Retourne le nombre total d'unités réservées pour un produit sur une période.
     */
    public function countReservedQtyForProduct(int $productId, string $startDate, string $endDate): int
    {
        $stmt = $this->connection->prepare(
            "SELECT COALESCE(SUM(ri.quantity), 0)
             FROM reservation_items ri
             JOIN reservations r ON r.id = ri.reservation_id
             WHERE ri.product_id = ?
               AND r.status IN ('pending','quoted','confirmed')
               AND r.start_date <= ? AND r.end_date >= ?"
        );
        $stmt->execute([$productId, $endDate, $startDate]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retourne les périodes de réservation (confirmées) pour un produit — pour le calendrier.
     * @return array<array{start: string, end: string, qty: int}>
     */
    public function getReservedPeriodsByProduct(int $productId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT r.start_date, r.end_date, r.status, ri.quantity
             FROM reservation_items ri
             JOIN reservations r ON r.id = ri.reservation_id
             WHERE ri.product_id = ?
               AND r.status IN ('confirmed','quoted')
             ORDER BY r.start_date"
        );
        $stmt->execute([$productId]);
        return array_map(fn($row) => [
            'start'  => $row['start_date'],
            'end'    => $row['end_date'],
            'qty'    => (int) $row['quantity'],
            'status' => $row['status'],
        ], $stmt->fetchAll());
    }

    private function hydrateItem(array $row): ReservationItem
    {
        return new ReservationItem(
            id:                (int) $row['id'],
            reservationId:     (int) $row['reservation_id'],
            productId:         isset($row['product_id']) && $row['product_id'] !== null ? (int) $row['product_id'] : null,
            quantity:          (int) $row['quantity'],
            packId:            isset($row['pack_id']) && $row['pack_id'] !== null ? (int) $row['pack_id'] : null,
            unitPriceSnapshot: isset($row['unit_price_snapshot']) && $row['unit_price_snapshot'] !== null
                                   ? (float) $row['unit_price_snapshot']
                                   : null,
        );
    }
}
