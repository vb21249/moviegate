<?php

declare(strict_types=1);

namespace App\Modules\Review\Events;

use App\Common\Events\DomainEvent;

/**
 * Review domain event placeholder.
 */
final class ReviewEvent extends DomainEvent
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