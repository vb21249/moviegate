<?php

declare(strict_types=1);

namespace App\Modules\Feed\Transformers;

/**
 * Builds feed-specific API payload fragments.
 */
final class FeedTransformer
{
    /**
     * @return array<string, bool>
     */
    public function rebuildAcceptedPayload(): array
    {
        return [
            'accepted' => true,
        ];
    }
}
