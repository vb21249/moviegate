<?php

declare(strict_types=1);

namespace App\Modules\Rating\Services;

use App\Common\Services\AbstractService;
use App\Modules\Rating\Exceptions\RatingException;
use App\Modules\Rating\Interfaces\RatingRepositoryInterface;
use App\Modules\Rating\Interfaces\RatingServiceInterface;
use App\Modules\Rating\Mappers\RatingMapper;
use App\Modules\Rating\Requests\RatingRequest;
use App\Modules\Rating\Responses\RatingResponse;
use App\Modules\Rating\Transformers\RatingTransformer;

/**
 * Rating application service for public ratings and owner mutations.
 */
final class RatingService extends AbstractService implements RatingServiceInterface
{
    public function __construct(
        private readonly RatingRepositoryInterface $repository,
        private readonly RatingMapper $mapper,
        private readonly RatingTransformer $transformer,
    ) {
    }

    public function index(RatingRequest $request): RatingResponse
    {
        $ratings = array_map(
            fn (array $rating): array => $this->mapper->mapRating($rating)->toArray(),
            $this->repository->findRatings(
                $request->limit(),
                $request->offset(),
                $request->movieId(),
                $request->userId(),
                $request->score()
            )
        );

        return new RatingResponse([
            'items' => $ratings,
            'pagination' => $this->pagination(
                $request,
                $this->repository->countRatings($request->movieId(), $request->userId(), $request->score())
            ),
        ]);
    }

    public function view(int $ratingId): RatingResponse
    {
        return new RatingResponse([
            'rating' => $this->mapper->mapRating($this->findRatingOrFail($ratingId))->toArray(),
        ]);
    }

    public function create(RatingRequest $request, int $userId): RatingResponse
    {
        $attributes = $request->ratingAttributes();
        $movieId = (int) $attributes['movie_id'];

        if (!$this->repository->movieExists($movieId)) {
            throw new RatingException('Movie not found', 404, RatingException::CODE_MOVIE_NOT_FOUND);
        }

        $existingRating = $this->repository->findOwnedRatingForMovie($userId, $movieId);

        if ($existingRating !== null) {
            unset($attributes['movie_id']);
            $this->repository->updateRating((int) $existingRating['id'], $userId, $attributes);
            $rating = $this->repository->findOwnedRating((int) $existingRating['id'], $userId) ?? $existingRating;

            return new RatingResponse([
                'rating' => $this->mapper->mapRating($rating)->toArray(),
            ]);
        }

        return new RatingResponse([
            'rating' => $this->mapper->mapRating($this->repository->createRating($userId, $attributes))->toArray(),
        ]);
    }

    public function update(int $ratingId, RatingRequest $request, int $userId): RatingResponse
    {
        $rating = $this->findOwnedRatingOrFail($ratingId, $userId);
        $attributes = $request->ratingAttributes();
        unset($attributes['movie_id']);

        if ($attributes === []) {
            throw new RatingException(
                'At least one rating field must be provided',
                422,
                RatingException::CODE_VALIDATION_ERROR
            );
        }

        $this->repository->updateRating($ratingId, $userId, $attributes);
        $updatedRating = $this->repository->findOwnedRating($ratingId, $userId) ?? $rating;

        return new RatingResponse([
            'rating' => $this->mapper->mapRating($updatedRating)->toArray(),
        ]);
    }

    public function delete(int $ratingId, int $userId): RatingResponse
    {
        $this->findOwnedRatingOrFail($ratingId, $userId);
        $this->repository->softDeleteRating($ratingId, $userId);

        return new RatingResponse($this->transformer->deletedPayload($ratingId));
    }

    public function history(RatingRequest $request, int $userId): RatingResponse
    {
        $ratings = array_map(
            fn (array $rating): array => $this->mapper->mapRating($rating)->toArray(),
            $this->repository->findUserHistory($userId, $request->limit(), $request->offset(), $request->movieId())
        );

        return new RatingResponse([
            'items' => $ratings,
            'pagination' => $this->pagination(
                $request,
                $this->repository->countUserHistory($userId, $request->movieId())
            ),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function findRatingOrFail(int $ratingId): array
    {
        $rating = $this->repository->findRating($ratingId);

        if ($rating === null) {
            throw new RatingException('Rating not found', 404, RatingException::CODE_RATING_NOT_FOUND);
        }

        return $rating;
    }

    /**
     * @return array<string, mixed>
     */
    private function findOwnedRatingOrFail(int $ratingId, int $userId): array
    {
        $rating = $this->repository->findOwnedRating($ratingId, $userId);

        if ($rating === null) {
            throw new RatingException('Rating not found', 404, RatingException::CODE_RATING_NOT_FOUND);
        }

        return $rating;
    }

    /**
     * @return array<string, int|bool>
     */
    private function pagination(RatingRequest $request, int $total): array
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
