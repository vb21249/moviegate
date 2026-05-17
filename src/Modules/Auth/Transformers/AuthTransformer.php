<?php

declare(strict_types=1);

namespace App\Modules\Auth\Transformers;

use App\Modules\Auth\Responses\AuthResponse;

/**
 * Auth transformer placeholder.
 */
final class AuthTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): AuthResponse
    {
        return new AuthResponse($payload);
    }
}