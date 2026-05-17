<?php

declare(strict_types=1);

namespace App\Modules\Notification\Interfaces;

/**
 * Notification repository contract.
 */
interface NotificationRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}