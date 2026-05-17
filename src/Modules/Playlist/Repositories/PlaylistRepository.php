<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Playlist\Interfaces\PlaylistRepositoryInterface;

/**
 * Playlist repository placeholder.
 */
final class PlaylistRepository extends BaseRepository implements PlaylistRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}