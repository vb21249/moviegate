<?php

declare(strict_types=1);

namespace App\Modules\User\Interfaces;

/**
 * User application service contract.
 */
interface UserServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}