<?php

declare(strict_types=1);

namespace App\Common\Events;

final class SyncDomainEventDispatcher implements DomainEventDispatcherInterface
{
    public function __construct(
        private readonly DomainEventSubscriberRegistry $registry,
    ) {
    }

    public function dispatch(DomainEvent $event): void
    {
        foreach ($this->registry->subscribersFor($event) as $subscriber) {
            $subscriber->handle($event);
        }
    }
}
