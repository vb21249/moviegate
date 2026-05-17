<?php

declare(strict_types=1);

namespace App\Modules\Movie\Events;

use App\Common\Events\DomainEvent;

/**
 * Movie domain event placeholder.
 */
final class MovieEvent extends DomainEvent
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