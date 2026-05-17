<?php

declare(strict_types=1);

namespace App\Modules\Review\Interfaces;

/**
 * Review application service contract.
 */
interface ReviewServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}