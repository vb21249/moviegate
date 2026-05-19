<?php

declare(strict_types=1);

namespace App\Modules\User\Services;

use App\Common\Services\AbstractService;
use App\Modules\User\Exceptions\UserException;
use App\Modules\User\Interfaces\UserRepositoryInterface;
use App\Modules\User\Interfaces\UserServiceInterface;
use App\Modules\User\Mappers\UserMapper;
use App\Modules\User\Requests\UserRequest;
use App\Modules\User\Responses\UserResponse;
use App\Modules\User\Transformers\UserTransformer;

/**
 * User application service for public profile and activity reads.
 */
final class UserService extends AbstractService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly UserMapper $mapper,
        private readonly UserTransformer $transformer,
    ) {
    }

    public function index(UserRequest $request): UserResponse
    {
        $profiles = array_map(
            fn (array $profile): array => $this->mapper
                ->mapProfile($profile, $this->repository->getProfileStats((int) $profile['id']))
                ->toArray(),
            $this->repository->findPublicProfiles($request->limit(), $request->offset())
        );

        return new UserResponse([
            'items' => $profiles,
            'pagination' => $this->pagination($request, $this->repository->countPublicProfiles()),
        ]);
    }

    public function view(int $userId): UserResponse
    {
        $profile = $this->findProfileOrFail($userId);

        return new UserResponse([
            'user' => $this->mapper
                ->mapProfile($profile, $this->repository->getProfileStats($userId))
                ->toArray(),
        ]);
    }

    public function activity(int $userId, UserRequest $request): UserResponse
    {
        $this->findProfileOrFail($userId);

        return new UserResponse([
            'items' => array_map(
                fn (array $row): array => $this->transformer->activityItem($row),
                $this->repository->findActivity($userId, $request->limit(), $request->offset())
            ),
            'pagination' => $this->pagination($request, $this->repository->countActivity($userId)),
        ]);
    }

    public function recentRatings(int $userId, UserRequest $request): UserResponse
    {
        $this->findProfileOrFail($userId);

        return new UserResponse([
            'items' => array_map(
                fn (array $row): array => $this->transformer->ratingItem($row),
                $this->repository->findRecentRatings($userId, $request->limit(), $request->offset())
            ),
            'pagination' => $this->pagination($request, $this->repository->countRecentRatings($userId)),
        ]);
    }

    public function posts(int $userId, UserRequest $request): UserResponse
    {
        $this->findProfileOrFail($userId);

        return new UserResponse([
            'items' => array_map(
                fn (array $row): array => $this->transformer->postItem($row),
                $this->repository->findPosts($userId, $request->limit(), $request->offset())
            ),
            'pagination' => $this->pagination($request, $this->repository->countPosts($userId)),
        ]);
    }

    /**
     * @param int $userId
     *
     * @return array<string, mixed>
     */
    private function findProfileOrFail(int $userId): array
    {
        $profile = $this->repository->findPublicProfile($userId);

        if ($profile === null) {
            throw new UserException('User not found', 404, UserException::CODE_USER_NOT_FOUND);
        }

        return $profile;
    }

    /**
     * @param UserRequest $request
     * @param int $total
     *
     * @return array<string, int|bool>
     */
    private function pagination(UserRequest $request, int $total): array
    {
        $limit = $request->limit();
        $offset = $request->offset();

        return [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
            'has_more' => $offset + $limit < $total,
        ];
    }
}
