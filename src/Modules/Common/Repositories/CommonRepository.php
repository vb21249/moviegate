<?php

declare(strict_types=1);

namespace App\Modules\Common\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Common\Interfaces\CommonRepositoryInterface;

/**
 * Common repository placeholder.
 */
final class CommonRepository extends BaseRepository implements CommonRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}