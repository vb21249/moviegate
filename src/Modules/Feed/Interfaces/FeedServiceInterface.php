<?php

declare(strict_types=1);

namespace App\Modules\Feed\Interfaces;

/**
 * Feed application service contract.
 */
interface FeedServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}