<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Auth\Interfaces\AuthRepositoryInterface;

/**
 * Auth repository placeholder.
 */
final class AuthRepository extends BaseRepository implements AuthRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}