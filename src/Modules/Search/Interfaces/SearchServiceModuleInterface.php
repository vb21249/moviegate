<?php

declare(strict_types=1);

namespace App\Modules\Search\Interfaces;

/**
 * Search application service contract.
 */
interface SearchServiceModuleInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}