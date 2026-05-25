<?php

declare(strict_types=1);

namespace App\Common\Events;

interface DomainEventSubscriberInterface
{
    /**
     * @return list<string>
     */
    public function subscribedTo(): array;

    public function handle(DomainEvent $event): void;
}
