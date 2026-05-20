<?php

declare(strict_types=1);

namespace App\Modules\Integration\Interfaces;

/**
 * Integration repository contract.
 */
interface IntegrationRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;

    /**
     * @param string $correlationId
     * @param string $service
     * @param string $operation
     * @param array<string, mixed> $requestPayload
     * @param array<string, mixed> $responsePayload
     * @param string $status
     *
     * @return int
     */
    public function logIntegration(
        string $correlationId,
        string $service,
        string $operation,
        array $requestPayload,
        array $responsePayload,
        string $status
    ): int;
}
