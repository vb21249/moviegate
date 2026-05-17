<?php

declare(strict_types=1);

namespace App\Modules\User\Services;

use App\Common\Services\AbstractService;
use App\Modules\User\Interfaces\UserRepositoryInterface;
use App\Modules\User\Interfaces\UserServiceInterface;

/**
 * User application service placeholder.
 */
final class UserService extends AbstractService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'User',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}