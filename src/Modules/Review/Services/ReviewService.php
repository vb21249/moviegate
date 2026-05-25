<?php

declare(strict_types=1);

namespace App\Modules\Review\Services;

use App\Common\Events\DomainEventBusInterface;
use App\Common\Services\AbstractService;
use App\Modules\Review\Enums\ReviewStatus;
use App\Modules\Review\Events\ReviewEvent;
use App\Modules\Review\Exceptions\ReviewException;
use App\Modules\Review\Interfaces\ReviewRepositoryInterface;
use App\Modules\Review\Interfaces\ReviewServiceInterface;
use App\Modules\Review\Mappers\ReviewMapper;
use App\Modules\Review\Requests\ReviewRequest;
use App\Modules\Review\Responses\ReviewResponse;
use App\Modules\Review\Transformers\ReviewTransformer;

/**
 * Review application service for public reviews and owner mutations.
 */
final class ReviewService extends AbstractService implements ReviewServiceInterface
{
    public function __construct(
        private readonly ReviewRepositoryInterface $repository,
        private readonly ReviewMapper $mapper,
        private readonly ReviewTransformer $transformer,
        private readonly ?DomainEventBusInterface $eventBus = null,
    ) {
    }

    public function index(ReviewRequest $request): ReviewResponse
    {
        $reviews = array_map(
            fn (array $review): array => $this->mapper->mapReview($review)->toArray(),
            $this->repository->findPublishedReviews(
                $request->limit(),
                $request->offset(),
                $request->query(),
                $request->movieId(),
                $request->userId()
            )
        );

        return new ReviewResponse([
            'items' => $reviews,
            'pagination' => $this->pagination(
                $request,
                $this->repository->countPublishedReviews($request->query(), $request->movieId(), $request->userId())
            ),
        ]);
    }

    public function view(int $reviewId): ReviewResponse
    {
        return new ReviewResponse([
            'review' => $this->mapper->mapReview($this->findPublishedReviewOrFail($reviewId))->toArray(),
        ]);
    }

    public function create(ReviewRequest $request, int $userId): ReviewResponse
    {
        $attributes = $request->reviewAttributes();
        $movieId = (int) $attributes['movie_id'];
        $this->validateMovieAndRating($movieId, $request->ratingId(), $userId);
        $attributes['status'] = $attributes['status'] ?? ReviewStatus::Published->value;

        $review = $this->repository->createReview($userId, $attributes);
        $this->publishPublishedReviewEvent(ReviewEvent::CREATED, $review);

        return new ReviewResponse([
            'review' => $this->mapper->mapReview($review)->toArray(),
        ]);
    }

    public function update(int $reviewId, ReviewRequest $request, int $userId): ReviewResponse
    {
        $review = $this->findOwnedReviewOrFail($reviewId, $userId);
        $attributes = $request->reviewAttributes();

        if ($attributes === []) {
            throw new ReviewException(
                'At least one review field must be provided',
                422,
                ReviewException::CODE_VALIDATION_ERROR
            );
        }

        $movieId = (int) $review['movie_id'];
        $this->validateMovieAndRating($movieId, $request->ratingId(), $userId);
        unset($attributes['movie_id']);

        $this->repository->updateReview($reviewId, $userId, $attributes);
        $updatedReview = $this->repository->findOwnedReview($reviewId, $userId) ?? $review;
        $this->publishPublishedReviewEvent(
            (($review['status'] ?? null) === ReviewStatus::Published->value)
                ? ReviewEvent::UPDATED
                : ReviewEvent::PUBLISHED,
            $updatedReview
        );

        return new ReviewResponse([
            'review' => $this->mapper->mapReview($updatedReview)->toArray(),
        ]);
    }

    public function delete(int $reviewId, int $userId): ReviewResponse
    {
        $this->findOwnedReviewOrFail($reviewId, $userId);
        $this->repository->softDeleteReview($reviewId, $userId);

        return new ReviewResponse($this->transformer->deletedPayload($reviewId));
    }

    /**
     * @return array<string, mixed>
     */
    private function findPublishedReviewOrFail(int $reviewId): array
    {
        $review = $this->repository->findPublishedReview($reviewId);

        if ($review === null) {
            throw new ReviewException('Review not found', 404, ReviewException::CODE_REVIEW_NOT_FOUND);
        }

        return $review;
    }

    /**
     * @return array<string, mixed>
     */
    private function findOwnedReviewOrFail(int $reviewId, int $userId): array
    {
        $review = $this->repository->findOwnedReview($reviewId, $userId);

        if ($review === null) {
            throw new ReviewException('Review not found', 404, ReviewException::CODE_REVIEW_NOT_FOUND);
        }

        return $review;
    }

    private function validateMovieAndRating(int $movieId, ?int $ratingId, int $userId): void
    {
        if (!$this->repository->movieExists($movieId)) {
            throw new ReviewException('Movie not found', 404, ReviewException::CODE_MOVIE_NOT_FOUND);
        }

        if ($ratingId !== null && !$this->repository->ratingBelongsToUserMovie($ratingId, $userId, $movieId)) {
            throw new ReviewException('Rating not found', 404, ReviewException::CODE_RATING_NOT_FOUND);
        }
    }

    /**
     * @return array<string, int|bool>
     */
    private function pagination(ReviewRequest $request, int $total): array
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

    /**
     * @param array<string, mixed> $review
     */
    private function publishPublishedReviewEvent(string $eventName, array $review): void
    {
        if ($this->eventBus === null || ($review['status'] ?? null) !== ReviewStatus::Published->value) {
            return;
        }

        $this->eventBus->publish(new ReviewEvent(
            $eventName,
            (int) $review['user_id'],
            (int) $review['id'],
            [
                'review_id' => (int) $review['id'],
                'movie_id' => (int) $review['movie_id'],
                'movie_slug' => $review['movie_slug'] !== null ? (string) $review['movie_slug'] : null,
                'movie_title' => $review['movie_title'] !== null ? (string) $review['movie_title'] : null,
                'movie_poster_url' => $review['movie_poster_url'] !== null
                    ? (string) $review['movie_poster_url']
                    : null,
                'title' => (string) $review['title'],
                'published_at' => $review['published_at'] !== null ? (string) $review['published_at'] : null,
            ],
        ));
    }
}
