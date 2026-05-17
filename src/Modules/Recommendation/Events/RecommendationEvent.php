<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Events;

use App\Common\Events\DomainEvent;

/**
 * Recommendation domain event placeholder.
 */
final class RecommendationEvent extends DomainEvent
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
        parent::__construct();
    }
}