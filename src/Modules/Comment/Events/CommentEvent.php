<?php

declare(strict_types=1);

namespace App\Modules\Comment\Events;

use App\Common\Events\DomainEvent;

/**
 * Comment domain event placeholder.
 */
final class CommentEvent extends DomainEvent
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