<?php

declare(strict_types=1);

namespace App\Modules\Rating\Events;

use App\Common\Events\DomainActivityEventInterface;
use App\Common\Events\DomainEvent;

/**
 * Rating domain event.
 */
final class RatingEvent extends DomainEvent implements DomainActivityEventInterface
{
    public const CREATED = 'rating_created';
    public const UPDATED = 'rating_updated';

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly string $name,
        private readonly int $actorId,
        private readonly ?int $entityId,
        public readonly array $payload = [],
    ) {
        parent::__construct();
    }

    public function eventName(): string
    {
        return $this->name;
    }

    public function actorId(): int
    {
        return $this->actorId;
    }

    public function entityType(): string
    {
        return 'rating';
    }

    public function entityId(): ?int
    {
        return $this->entityId;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
