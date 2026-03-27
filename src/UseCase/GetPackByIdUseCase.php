<?php

declare(strict_types=1);

namespace Rore\UseCase;

use Rore\Entity\Pack;
use Rore\Port\PackRepositoryInterface;
use Rore\Adapter\MySqlPackRepository;
use RRB\Di\BindAdapter;

/**
 * Récupère un pack par son ID.
 */
final class GetPackByIdUseCase
{
    public function __construct(
        #[BindAdapter(MySqlPackRepository::class)]
        private readonly PackRepositoryInterface $packRepo,
    ) {}

    public function execute(int $id): ?Pack
    {
        return $this->packRepo->findById($id);
    }
}
