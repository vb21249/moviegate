<?php

declare(strict_types=1);

namespace App\Modules\Feed\Subscribers;

use App\Common\Events\DomainActivityEventInterface;
use App\Common\Events\DomainEvent;
use App\Common\Events\DomainEventSubscriberInterface;
use App\Modules\Feed\Interfaces\FeedRepositoryInterface;

/**
 * Projects domain activity events into the public activity feed.
 */
final class SocialActivityFeedSubscriber implements DomainEventSubscriberInterface
{
    public function __construct(
        private readonly FeedRepositoryInterface $repository,
    ) {
    }

    public function subscribedTo(): array
    {
        return [DomainActivityEventInterface::class];
    }

    public function handle(DomainEvent $event): void
    {
        if (!$event instanceof DomainActivityEventInterface) {
            return;
        }

        $payload = $event->payload();
        unset($payload['recipient_user_id']);

        $payload['event_occurred_at'] = $event->occurredAt->format(DATE_ATOM);

        $this->repository->appendEvent(
            $event->actorId(),
            $event->eventName(),
            $event->entityType(),
            $event->entityId(),
            $payload,
        );
    }
}
