<?php

declare(strict_types=1);

namespace App\Modules\Common\Interfaces;

/**
 * Common repository contract.
 */
interface CommonRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function findApiLogs(
        int $limit,
        int $offset,
        ?string $correlationId = null,
        ?string $requestMethod = null,
        ?int $responseStatus = null,
        ?string $createdFrom = null,
        ?string $createdTo = null
    ): array;

    public function countApiLogs(
        ?string $correlationId = null,
        ?string $requestMethod = null,
        ?int $responseStatus = null,
        ?string $createdFrom = null,
        ?string $createdTo = null
    ): int;

    /**
     * @return array<string, mixed>|null
     */
    public function findApiLog(int $id): ?array;

    /**
     * @return array<string, mixed>
     */
    public function apiLogSummary(?string $createdFrom = null, ?string $createdTo = null): array;

    public function countApiLogsOlderThan(string $threshold): int;

    public function deleteApiLogsOlderThan(string $threshold): int;

    public function countIntegrationLogsOlderThan(string $threshold): int;

    public function deleteIntegrationLogsOlderThan(string $threshold): int;
}
