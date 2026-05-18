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
     * @param array<string, mixed> $payload
     * @param int $ttl
     * @param string $type
     */
    public function issueToken(array $payload, int $ttl, string $type): string;

    /**
     * @return array<string, mixed>
     */
    public function parse(string $token): array;
}
