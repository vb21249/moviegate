<?php

declare(strict_types=1);

namespace App\Modules\Feed\Services;

use App\Common\Services\AbstractService;
use App\Modules\Feed\Interfaces\FeedRepositoryInterface;
use App\Modules\Feed\Interfaces\FeedServiceInterface;

/**
 * Feed application service placeholder.
 */
final class FeedService extends AbstractService implements FeedServiceInterface
{
    public function __construct(
        private readonly FeedRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Feed',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}