<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Interfaces;

/**
 * Recommendation application service contract.
 */
interface RecommendationServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}