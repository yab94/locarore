<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Infrastructure\Database\MysqlDatabase;
use Rore\Domain\Faq\Entity\FaqItem;
use Rore\Application\Faq\Port\FaqRepositoryInterface;

class MySqlFaqRepositoryAdapter implements FaqRepositoryInterface
{
    public function __construct(
        private readonly MysqlDatabase $connection,
    ) {}

    public function findAll(): array
    {
        $stmt = $this->connection->query(
            'SELECT * FROM faq_items ORDER BY position ASC, id ASC'
        );
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findAllVisible(): array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM faq_items WHERE is_visible = 1 ORDER BY position ASC, id ASC'
        );
        $stmt->execute();
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findById(int $id): ?FaqItem
    {
        $stmt = $this->connection->prepare('SELECT * FROM faq_items WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function save(FaqItem $item): void
    {
        if ($item->getId() === null) {
            $stmt = $this->connection->prepare(
                'INSERT INTO faq_items (question, answer, position, is_visible, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $item->getQuestion(),
                $item->getAnswer(),
                $item->getPosition(),
                (int) $item->isVisible(),
                $item->getCreatedAt()->format('Y-m-d H:i:s'),
                $item->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]);
        } else {
            $stmt = $this->connection->prepare(
                'UPDATE faq_items
                    SET question = ?, answer = ?, position = ?, is_visible = ?, updated_at = ?
                  WHERE id = ?'
            );
            $stmt->execute([
                $item->getQuestion(),
                $item->getAnswer(),
                $item->getPosition(),
                (int) $item->isVisible(),
                $item->getUpdatedAt()->format('Y-m-d H:i:s'),
                $item->getId(),
            ]);
        }
    }

    public function delete(int $id): void
    {
        $this->connection->prepare('DELETE FROM faq_items WHERE id = ?')->execute([$id]);
    }

    private function hydrate(array $row): FaqItem
    {
        return new FaqItem(
            id:        (int) $row['id'],
            question:  $row['question'],
            answer:    $row['answer'],
            position:  (int) $row['position'],
            isVisible: (bool) $row['is_visible'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at']),
        );
    }
}
