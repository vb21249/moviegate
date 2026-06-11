<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Common\Events\DomainEvent;
use App\Common\Events\DomainEventBusInterface;

final class CapturingDomainEventBus implements DomainEventBusInterface
{
    /**
     * @var list<DomainEvent>
     */
    public array $events = [];

    public function publish(DomainEvent $event): void
    {
        $this->events[] = $event;
    }
}
