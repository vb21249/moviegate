<?php

declare(strict_types=1);

namespace App\Common\Events;

interface DomainEventDispatcherInterface
{
    public function dispatch(DomainEvent $event): void;
}
