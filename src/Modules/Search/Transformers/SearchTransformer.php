<?php

declare(strict_types=1);

namespace App\Modules\Search\Transformers;

use App\Modules\Search\Responses\SearchResponse;

/**
 * Search transformer placeholder.
 */
final class SearchTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): SearchResponse
    {
        return new SearchResponse($payload);
    }
}