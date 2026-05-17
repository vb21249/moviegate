<?php

declare(strict_types=1);

namespace App\Modules\Integration\Interfaces;

/**
 * Integration application service contract.
 */
interface IntegrationServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}