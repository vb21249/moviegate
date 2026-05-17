<?php

declare(strict_types=1);

namespace App\Modules\Review\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Review\Interfaces\ReviewRepositoryInterface;

/**
 * Review repository placeholder.
 */
final class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}