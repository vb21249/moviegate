<?php

declare(strict_types=1);

namespace App\Modules\Movie\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Movie\Interfaces\MovieRepositoryInterface;

/**
 * Movie repository placeholder.
 */
final class MovieRepository extends BaseRepository implements MovieRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}