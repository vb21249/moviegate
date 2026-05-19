<?php

declare(strict_types=1);

namespace App\Modules\Rating\Events;

use App\Common\Events\DomainEvent;

/**
 * Rating domain event.
 */
final class RatingEvent extends DomainEvent
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
