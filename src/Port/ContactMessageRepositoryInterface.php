<?php

declare(strict_types=1);

namespace Rore\Port;

use Rore\Entity\ContactMessage;
use Rore\Adapter\MySqlContactMessageRepository;

interface ContactMessageRepositoryInterface
{
    /** @return ContactMessage[] */
    public function findAll(): array;

    /** @return ContactMessage[] */
    public function findUnread(): array;

    public function findById(int $id): ?ContactMessage;

    public function countUnread(): int;

    public function save(ContactMessage $message): void;

    public function delete(int $id): void;
}
