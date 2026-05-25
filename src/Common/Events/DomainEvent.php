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

    public function eventName(): string
    {
        return static::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        if (property_exists($this, 'payload') && is_array($this->payload)) {
            return $this->payload;
        }

        return [];
    }
}
