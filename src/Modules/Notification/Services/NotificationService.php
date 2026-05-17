<?php

declare(strict_types=1);

namespace App\Modules\Notification\Services;

use App\Common\Services\AbstractService;
use App\Modules\Notification\Interfaces\NotificationRepositoryInterface;
use App\Modules\Notification\Interfaces\NotificationServiceInterface;

/**
 * Notification application service placeholder.
 */
final class NotificationService extends AbstractService implements NotificationServiceInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Notification',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}