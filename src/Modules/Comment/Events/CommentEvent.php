<?php

declare(strict_types=1);

namespace App\Modules\Comment\Events;

use App\Common\Events\DomainActivityEventInterface;
use App\Common\Events\DomainEvent;

/**
 * Comment domain event.
 */
final class CommentEvent extends DomainEvent implements DomainActivityEventInterface
{
    public const CREATED = 'comment_created';
    public const REPLIED = 'comment_replied';
    public const LIKED = 'comment_liked';

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
        return 'comment';
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
