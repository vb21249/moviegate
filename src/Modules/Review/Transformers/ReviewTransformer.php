<?php

declare(strict_types=1);

namespace App\Modules\Review\Transformers;

/**
 * Builds review-specific API payload fragments.
 */
final class ReviewTransformer
{
    /**
     * @return array<string, int|bool>
     */
    public function deletedPayload(int $reviewId): array
    {
        return [
            'deleted' => true,
            'review_id' => $reviewId,
        ];
    }
}
