<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Transformers;

use App\Modules\Recommendation\Responses\RecommendationResponse;

/**
 * Recommendation transformer placeholder.
 */
final class RecommendationTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): RecommendationResponse
    {
        return new RecommendationResponse($payload);
    }
}