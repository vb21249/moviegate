<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Services;

use App\Common\Services\AbstractService;
use App\Modules\Playlist\Interfaces\PlaylistRepositoryInterface;
use App\Modules\Playlist\Interfaces\PlaylistServiceInterface;

/**
 * Playlist application service placeholder.
 */
final class PlaylistService extends AbstractService implements PlaylistServiceInterface
{
    public function __construct(
        private readonly PlaylistRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Playlist',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}