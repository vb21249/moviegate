<?php

declare(strict_types=1);

namespace App\Common\Events;

use App\Common\Jobs\DispatchDomainEventJob;
use Yii;
use yii\queue\Queue;

final class QueuedDomainEventBus implements DomainEventBusInterface
{
    public function __construct(
        private readonly ?Queue $queue = null,
    ) {
    }

    public function publish(DomainEvent $event): void
    {
        $queue = $this->queue ?? $this->queueComponent();

        if ($queue instanceof Queue) {
            $queue->push(new DispatchDomainEventJob($event));
            return;
        }

        Yii::$container->get(DomainEventDispatcherInterface::class)->dispatch($event);
    }

    private function queueComponent(): ?Queue
    {
        if (Yii::$app === null || !Yii::$app->has('queue')) {
            return null;
        }

        $queue = Yii::$app->get('queue', false);

        return $queue instanceof Queue ? $queue : null;
    }
}
