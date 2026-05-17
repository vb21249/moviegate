<?php

declare(strict_types=1);

namespace App\Modules\Search\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Search\Interfaces\SearchRepositoryInterface;

/**
 * Search repository placeholder.
 */
final class SearchRepository extends BaseRepository implements SearchRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}