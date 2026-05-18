<?php

declare(strict_types=1);

namespace App\Modules\User\Interfaces;

use App\Modules\User\Requests\UserRequest;
use App\Modules\User\Responses\UserResponse;

/**
 * User application service contract.
 */
interface UserServiceInterface
{
    /**
     * Returns public user profiles.
     *
     * @param UserRequest $request
     *
     * @return UserResponse
     */
    public function index(UserRequest $request): UserResponse;

    /**
     * Returns a public user profile by id.
     *
     * @param int $userId
     *
     * @return UserResponse
     */
    public function view(int $userId): UserResponse;

    /**
     * Returns recent user activity feed events.
     *
     * @param int $userId
     * @param UserRequest $request
     *
     * @return UserResponse
     */
    public function activity(int $userId, UserRequest $request): UserResponse;

    /**
     * Returns recent user ratings with movie summaries.
     *
     * @param int $userId
     * @param UserRequest $request
     *
     * @return UserResponse
     */
    public function recentRatings(int $userId, UserRequest $request): UserResponse;

    /**
     * Returns published user review posts.
     *
     * @param int $userId
     * @param UserRequest $request
     *
     * @return UserResponse
     */
    public function posts(int $userId, UserRequest $request): UserResponse;
}
