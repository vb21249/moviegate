<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Mappers;

use App\Modules\Playlist\DTO\PlaylistDto;
use App\Modules\Playlist\Entities\PlaylistEntity;

/**
 * Playlist mapper placeholder.
 */
final class PlaylistMapper
{
    public function mapToDto(PlaylistEntity $entity): PlaylistDto
    {
        return new PlaylistDto($entity->attributes);
    }
}