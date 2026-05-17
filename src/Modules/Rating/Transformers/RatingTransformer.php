<?php

declare(strict_types=1);

namespace App\Modules\Rating\Transformers;

use App\Modules\Rating\Responses\RatingResponse;

/**
 * Rating transformer placeholder.
 */
final class RatingTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): RatingResponse
    {
        return new RatingResponse($payload);
    }
}