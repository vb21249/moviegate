<?php

declare(strict_types=1);

namespace App\Common\Events;

use DateTimeImmutable;

/**
 * Base domain event.
 */
abstract class DomainEvent
{
    public readonly DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->occurredAt = new DateTimeImmutable();
    }
}
