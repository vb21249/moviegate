<?php

declare(strict_types=1);

namespace App\Modules\Integration\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Integration\Interfaces\IntegrationRepositoryInterface;

/**
 * Integration repository placeholder.
 */
final class IntegrationRepository extends BaseRepository implements IntegrationRepositoryInterface
{
    public function query(array $criteria = []): array
    {
        return [];
    }
}