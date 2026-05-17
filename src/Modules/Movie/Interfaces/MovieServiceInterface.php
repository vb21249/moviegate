<?php

declare(strict_types=1);

namespace App\Modules\Movie\Interfaces;

/**
 * Movie application service contract.
 */
interface MovieServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}