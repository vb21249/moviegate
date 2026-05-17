<?php

declare(strict_types=1);

namespace App\Modules\Common\Events;

use App\Common\Events\DomainEvent;

/**
 * Common domain event placeholder.
 */
final class CommonEvent extends DomainEvent
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