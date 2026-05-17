<?php

declare(strict_types=1);

namespace App\Modules\Integration\Clients;

/**
 * Legacy SOAP gateway contract.
 */
interface LegacySoapGatewayInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function call(string $operation, array $payload = []): array;
}
