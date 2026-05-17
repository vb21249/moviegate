<?php

declare(strict_types=1);

namespace App\Modules\Common\Interfaces;

/**
 * Common application service contract.
 */
interface CommonServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}