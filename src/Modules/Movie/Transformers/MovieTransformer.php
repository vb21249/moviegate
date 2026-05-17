<?php

declare(strict_types=1);

namespace App\Modules\Movie\Transformers;

use App\Modules\Movie\Responses\MovieResponse;

/**
 * Movie transformer placeholder.
 */
final class MovieTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): MovieResponse
    {
        return new MovieResponse($payload);
    }
}