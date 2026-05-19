<?php

declare(strict_types=1);

namespace App\Modules\Rating\Transformers;

/**
 * Builds rating-specific API payload fragments.
 */
final class RatingTransformer
{
    /**
     * @return array<string, int|bool>
     */
    public function deletedPayload(int $ratingId): array
    {
        return [
            'deleted' => true,
            'rating_id' => $ratingId,
        ];
    }
}
