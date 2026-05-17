<?php

declare(strict_types=1);

namespace App\Modules\Auth\Interfaces;

/**
 * Auth application service contract.
 */
interface AuthServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}