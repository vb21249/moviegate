<?php

declare(strict_types=1);

namespace App\Modules\Comment\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Comment\Interfaces\CommentRepositoryInterface;

/**
 * Comment repository placeholder.
 */
final class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}