<?php

declare(strict_types=1);

namespace App\Modules\Auth\Transformers;

use App\Modules\Auth\Responses\AuthResponse;

/**
 * Transforms auth response DTO into API-friendly array payload.
 */
final class AuthTransformer
{
    /**
     * @param AuthResponse $response
     *
     * @return array<string, mixed>
     */
    public function transform(AuthResponse $response): array
    {
        return $response->toArray();
    }
}
