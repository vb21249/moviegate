<?php

declare(strict_types=1);

namespace App\Modules\Feed\Transformers;

use App\Modules\Feed\Responses\FeedResponse;

/**
 * Feed transformer placeholder.
 */
final class FeedTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): FeedResponse
    {
        return new FeedResponse($payload);
    }
}