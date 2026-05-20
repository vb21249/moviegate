<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Interfaces;

use App\Modules\Recommendation\Requests\RecommendationRequest;
use App\Modules\Recommendation\Responses\RecommendationResponse;

/**
 * Recommendation application service contract.
 */
interface RecommendationServiceInterface
{
    public function index(RecommendationRequest $request, ?int $userId = null): RecommendationResponse;

    public function rebuild(RecommendationRequest $request, int $userId): RecommendationResponse;
}
