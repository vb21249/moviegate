<?php

declare(strict_types=1);

namespace App\Modules\Integration\Events;

use App\Common\Events\DomainEvent;

/**
 * Integration domain event placeholder.
 */
final class IntegrationEvent extends DomainEvent
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