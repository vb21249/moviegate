<?php

declare(strict_types=1);

namespace App\Modules\Feed\Events;

use App\Common\Events\DomainEvent;

/**
 * Feed domain event.
 */
final class FeedEvent extends DomainEvent
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
