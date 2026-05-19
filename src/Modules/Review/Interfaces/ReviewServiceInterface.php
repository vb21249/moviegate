<?php

declare(strict_types=1);

namespace App\Modules\Review\Interfaces;

use App\Modules\Review\Requests\ReviewRequest;
use App\Modules\Review\Responses\ReviewResponse;

/**
 * Review application service contract.
 */
interface ReviewServiceInterface
{
    /**
     * @param ReviewRequest $request
     *
     * @return ReviewResponse
     */
    public function index(ReviewRequest $request): ReviewResponse;

    /**
     * @param int $reviewId
     *
     * @return ReviewResponse
     */
    public function view(int $reviewId): ReviewResponse;

    /**
     * @param ReviewRequest $request
     * @param int $userId
     *
     * @return ReviewResponse
     */
    public function create(ReviewRequest $request, int $userId): ReviewResponse;

    /**
     * @param int $reviewId
     * @param ReviewRequest $request
     * @param int $userId
     *
     * @return ReviewResponse
     */
    public function update(int $reviewId, ReviewRequest $request, int $userId): ReviewResponse;

    /**
     * @param int $reviewId
     * @param int $userId
     *
     * @return ReviewResponse
     */
    public function delete(int $reviewId, int $userId): ReviewResponse;
}
