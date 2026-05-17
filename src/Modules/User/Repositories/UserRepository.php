<?php

declare(strict_types=1);

namespace App\Modules\User\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\User\Interfaces\UserRepositoryInterface;

/**
 * User repository placeholder.
 */
final class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}