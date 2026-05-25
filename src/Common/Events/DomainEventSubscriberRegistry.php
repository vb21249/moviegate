<?php

declare(strict_types=1);

namespace App\Common\Events;

/**
 * Keeps subscriber wiring explicit and framework-independent.
 */
final class DomainEventSubscriberRegistry
{
    /**
     * @param iterable<DomainEventSubscriberInterface> $subscribers
     */
    public function __construct(
        private readonly iterable $subscribers = [],
    ) {
    }

    /**
     * @return list<DomainEventSubscriberInterface>
     */
    public function subscribersFor(DomainEvent $event): array
    {
        $matched = [];

        foreach ($this->subscribers as $subscriber) {
            foreach ($subscriber->subscribedTo() as $subscription) {
                if ($event->eventName() === $subscription || is_a($event, $subscription)) {
                    $matched[] = $subscriber;
                    break;
                }
            }
        }

        return $matched;
    }
}
