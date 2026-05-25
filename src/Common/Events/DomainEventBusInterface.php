<?php

declare(strict_types=1);

namespace App\Common\Events;

interface DomainEventBusInterface
{
    public function publish(DomainEvent $event): void;
}
