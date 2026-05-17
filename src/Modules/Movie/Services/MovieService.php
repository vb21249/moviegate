<?php

declare(strict_types=1);

namespace App\Modules\Movie\Services;

use App\Common\Services\AbstractService;
use App\Modules\Movie\Interfaces\MovieRepositoryInterface;
use App\Modules\Movie\Interfaces\MovieServiceInterface;

/**
 * Movie application service placeholder.
 */
final class MovieService extends AbstractService implements MovieServiceInterface
{
    public function __construct(
        private readonly MovieRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Movie',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}