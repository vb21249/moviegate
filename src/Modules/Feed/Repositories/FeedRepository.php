<?php

declare(strict_types=1);

namespace App\Modules\Feed\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Feed\Interfaces\FeedRepositoryInterface;

/**
 * Feed repository placeholder.
 */
final class FeedRepository extends BaseRepository implements FeedRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}