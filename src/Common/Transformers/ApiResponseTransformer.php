<?php

declare(strict_types=1);

namespace App\Common\Transformers;

use App\Common\Dto\ApiResponseDto;

/**
 * Maps API response DTOs to arrays.
 */
final class ApiResponseTransformer
{
    /**
     * @param ApiResponseDto $response
     *
     * @return array<string, mixed>
     */
    public function transform(ApiResponseDto $response): array
    {
        return $response->toArray();
    }
}
