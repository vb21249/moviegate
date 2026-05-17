<?php

declare(strict_types=1);

namespace App\Modules\User\Events;

use App\Common\Events\DomainEvent;

/**
 * User domain event placeholder.
 */
final class UserEvent extends DomainEvent
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