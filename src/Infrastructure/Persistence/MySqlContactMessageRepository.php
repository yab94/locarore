<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Domain\Contact\Entity\ContactMessage;
use Rore\Domain\Contact\Repository\ContactMessageRepositoryInterface;
use Rore\Framework\Database;

final class MySqlContactMessageRepository implements ContactMessageRepositoryInterface
{
    public function __construct(
        private readonly Database $connection
    ) {}

    public function findAll(): array
    {
        $stmt = $this->connection->query(
            'SELECT * FROM contact_messages ORDER BY created_at DESC'
        );
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findUnread(): array
    {
        $stmt = $this->connection->query(
            'SELECT * FROM contact_messages WHERE is_read = 0 ORDER BY created_at DESC'
        );
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findById(int $id): ?ContactMessage
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM contact_messages WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function countUnread(): int
    {
        $stmt = $this->connection->query(
            'SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'
        );
        return (int) $stmt->fetchColumn();
    }

    public function save(ContactMessage $message): void
    {
        if ($message->getId() === null) {
            $stmt = $this->connection->prepare(
                'INSERT INTO contact_messages
                 (first_name, last_name, email, phone, subject, content, is_read, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $message->getFirstName(),
                $message->getLastName(),
                $message->getEmail(),
                $message->getPhone(),
                $message->getSubject(),
                $message->getContent(),
                $message->isRead() ? 1 : 0,
                $message->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        } else {
            $stmt = $this->connection->prepare(
                'UPDATE contact_messages SET is_read = ? WHERE id = ?'
            );
            $stmt->execute([
                $message->isRead() ? 1 : 0,
                $message->getId(),
            ]);
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare(
            'DELETE FROM contact_messages WHERE id = ?'
        );
        $stmt->execute([$id]);
    }

    private function hydrate(array $row): ContactMessage
    {
        return new ContactMessage(
            id:        (int) $row['id'],
            firstName: $row['first_name'],
            lastName:  $row['last_name'],
            email:     $row['email'],
            phone:     $row['phone'],
            subject:   $row['subject'],
            content:   $row['content'],
            isRead:    (bool) $row['is_read'],
            createdAt: new \DateTimeImmutable($row['created_at']),
        );
    }
}
