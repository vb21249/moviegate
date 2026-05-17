<?php

declare(strict_types=1);

namespace App\Modules\Rating\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Rating\Interfaces\RatingRepositoryInterface;

/**
 * Rating repository placeholder.
 */
final class RatingRepository extends BaseRepository implements RatingRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}