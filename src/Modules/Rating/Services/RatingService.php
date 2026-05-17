<?php

declare(strict_types=1);

namespace App\Modules\Rating\Services;

use App\Common\Services\AbstractService;
use App\Modules\Rating\Interfaces\RatingRepositoryInterface;
use App\Modules\Rating\Interfaces\RatingServiceInterface;

/**
 * Rating application service placeholder.
 */
final class RatingService extends AbstractService implements RatingServiceInterface
{
    public function __construct(
        private readonly RatingRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Rating',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}