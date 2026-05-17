<?php

declare(strict_types=1);

namespace App\Modules\Search\Events;

use App\Common\Events\DomainEvent;

/**
 * Search domain event placeholder.
 */
final class SearchEvent extends DomainEvent
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