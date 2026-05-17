<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Common\Services\AbstractService;
use App\Modules\Auth\Interfaces\AuthRepositoryInterface;
use App\Modules\Auth\Interfaces\AuthServiceInterface;

/**
 * Auth application service placeholder.
 */
final class AuthService extends AbstractService implements AuthServiceInterface
{
    public function __construct(
        private readonly AuthRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Auth',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}