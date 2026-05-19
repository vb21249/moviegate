<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Events;

use App\Common\Events\DomainEvent;

/**
 * Playlist domain event.
 */
final class PlaylistEvent extends DomainEvent
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
