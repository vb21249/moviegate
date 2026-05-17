<?php

declare(strict_types=1);

namespace App\Modules\Comment\Services;

use App\Common\Services\AbstractService;
use App\Modules\Comment\Interfaces\CommentRepositoryInterface;
use App\Modules\Comment\Interfaces\CommentServiceInterface;

/**
 * Comment application service placeholder.
 */
final class CommentService extends AbstractService implements CommentServiceInterface
{
    public function __construct(
        private readonly CommentRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Comment',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}