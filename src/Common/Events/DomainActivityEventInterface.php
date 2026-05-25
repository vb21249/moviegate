<?php

declare(strict_types=1);

namespace App\Common\Events;

/**
 * Domain event that represents a user-visible activity.
 */
interface DomainActivityEventInterface
{
    public function eventName(): string;

    public function actorId(): int;

    public function entityType(): string;

    public function entityId(): ?int;

    /**
     * @return array<string, mixed>
     */
    public function payload(): array;
}
