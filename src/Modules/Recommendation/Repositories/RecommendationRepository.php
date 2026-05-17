<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Recommendation\Interfaces\RecommendationRepositoryInterface;

/**
 * Recommendation repository placeholder.
 */
final class RecommendationRepository extends BaseRepository implements RecommendationRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}