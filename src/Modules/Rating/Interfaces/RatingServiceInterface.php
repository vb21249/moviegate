<?php

declare(strict_types=1);

namespace App\Modules\Rating\Interfaces;

/**
 * Rating application service contract.
 */
interface RatingServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}