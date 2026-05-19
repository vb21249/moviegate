<?php

declare(strict_types=1);

namespace App\Modules\Rating\Interfaces;

use App\Modules\Rating\Requests\RatingRequest;
use App\Modules\Rating\Responses\RatingResponse;

/**
 * Rating application service contract.
 */
interface RatingServiceInterface
{
    public function index(RatingRequest $request): RatingResponse;

    public function view(int $ratingId): RatingResponse;

    public function create(RatingRequest $request, int $userId): RatingResponse;

    public function update(int $ratingId, RatingRequest $request, int $userId): RatingResponse;

    public function delete(int $ratingId, int $userId): RatingResponse;

    public function history(RatingRequest $request, int $userId): RatingResponse;
}
