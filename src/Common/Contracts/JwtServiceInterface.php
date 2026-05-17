<?php

declare(strict_types=1);

namespace App\Common\Contracts;

/**
 * JWT token contract.
 */
interface JwtServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function issueAccessToken(array $payload): string;

    /**
     * @param array<string, mixed> $payload
     */
    public function issueRefreshToken(array $payload): string;

    /**
     * @return array<string, mixed>
     */
    public function parse(string $token): array;
}
