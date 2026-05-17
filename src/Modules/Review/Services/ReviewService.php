<?php

declare(strict_types=1);

namespace App\Modules\Review\Services;

use App\Common\Services\AbstractService;
use App\Modules\Review\Interfaces\ReviewRepositoryInterface;
use App\Modules\Review\Interfaces\ReviewServiceInterface;

/**
 * Review application service placeholder.
 */
final class ReviewService extends AbstractService implements ReviewServiceInterface
{
    public function __construct(
        private readonly ReviewRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Review',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}