<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Services;

use App\Common\Services\AbstractService;
use App\Modules\Recommendation\Interfaces\RecommendationRepositoryInterface;
use App\Modules\Recommendation\Interfaces\RecommendationServiceInterface;

/**
 * Recommendation application service placeholder.
 */
final class RecommendationService extends AbstractService implements RecommendationServiceInterface
{
    public function __construct(
        private readonly RecommendationRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Recommendation',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}