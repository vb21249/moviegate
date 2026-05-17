<?php

declare(strict_types=1);

namespace App\Modules\Auth\Events;

use App\Common\Events\DomainEvent;

/**
 * Auth domain event placeholder.
 */
final class AuthEvent extends DomainEvent
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