<?php

declare(strict_types=1);

namespace App\Common\Jobs;

use App\Common\Events\DomainEvent;
use App\Common\Events\DomainEventDispatcherInterface;
use App\Common\Queue\BaseJob;
use Yii;

/**
 * Queue envelope that executes domain-event subscribers in a worker.
 */
final class DispatchDomainEventJob extends BaseJob
{
    public function __construct(
        public readonly DomainEvent $event,
        array $config = [],
    ) {
        parent::__construct($config);
    }

    public function execute($queue): void
    {
        Yii::$container->get(DomainEventDispatcherInterface::class)->dispatch($this->event);
    }
}
