<?php

declare(strict_types=1);

namespace App\Modules\Comment\Interfaces;

/**
 * Comment application service contract.
 */
interface CommentServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;
}