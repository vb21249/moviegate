<?php

declare(strict_types=1);

namespace App\Modules\Notification\Subscribers;

use App\Common\Events\DomainActivityEventInterface;
use App\Common\Events\DomainEvent;
use App\Common\Events\DomainEventSubscriberInterface;
use App\Modules\Notification\Interfaces\NotificationRepositoryInterface;

/**
 * Creates in-app notifications for recipient-aware domain activity events.
 */
final class SocialActivityNotificationSubscriber implements DomainEventSubscriberInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $repository,
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
        $recipientUserId = isset($payload['recipient_user_id']) ? (int) $payload['recipient_user_id'] : null;

        if ($recipientUserId === null || $recipientUserId === $event->actorId()) {
            return;
        }

        unset($payload['recipient_user_id']);

        $payload['actor_user_id'] = $event->actorId();
        $payload['entity_type'] = $event->entityType();
        $payload['entity_id'] = $event->entityId();
        $payload['event_occurred_at'] = $event->occurredAt->format(DATE_ATOM);

        $this->repository->createNotification($recipientUserId, $event->eventName(), $payload);
    }
}
