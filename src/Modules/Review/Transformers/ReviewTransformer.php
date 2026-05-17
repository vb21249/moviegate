<?php

declare(strict_types=1);

namespace App\Modules\Review\Transformers;

use App\Modules\Review\Responses\ReviewResponse;

/**
 * Review transformer placeholder.
 */
final class ReviewTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): ReviewResponse
    {
        return new ReviewResponse($payload);
    }
}