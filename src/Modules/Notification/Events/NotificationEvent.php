<?php

declare(strict_types=1);

namespace App\Modules\Notification\Events;

use App\Common\Events\DomainEvent;

/**
 * Notification domain event.
 */
final class NotificationEvent extends DomainEvent
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
        parent::__construct();
    }
}
