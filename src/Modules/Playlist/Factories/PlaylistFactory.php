<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Factories;

use App\Modules\Playlist\Entities\PlaylistEntity;

/**
 * Playlist factory placeholder.
 */
final class PlaylistFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): PlaylistEntity
    {
        return new PlaylistEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}