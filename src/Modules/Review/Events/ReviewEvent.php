<?php

declare(strict_types=1);

namespace App\Modules\Review\Events;

use App\Common\Events\DomainActivityEventInterface;
use App\Common\Events\DomainEvent;

/**
 * Review domain event.
 */
final class ReviewEvent extends DomainEvent implements DomainActivityEventInterface
{
    public const CREATED = 'review_created';
    public const PUBLISHED = 'review_published';
    public const UPDATED = 'review_updated';

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
        return 'review';
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
