<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Entities;

/**
 * Playlist domain entity.
 */
final class PlaylistEntity
{
    /**
     * @param int|string|null $id
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public readonly int|string|null $id = null,
        public readonly array $attributes = [],
    ) {
    }
}