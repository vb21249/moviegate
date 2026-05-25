<?php

declare(strict_types=1);

namespace App\Modules\Comment\Services;

use App\Common\Events\DomainEventBusInterface;
use App\Common\Services\AbstractService;
use App\Modules\Comment\Events\CommentEvent;
use App\Modules\Comment\Exceptions\CommentException;
use App\Modules\Comment\Interfaces\CommentRepositoryInterface;
use App\Modules\Comment\Interfaces\CommentServiceInterface;
use App\Modules\Comment\Mappers\CommentMapper;
use App\Modules\Comment\Requests\CommentRequest;
use App\Modules\Comment\Responses\CommentResponse;
use App\Modules\Comment\Transformers\CommentTransformer;

/**
 * Comment application service for public threads and owner mutations.
 */
final class CommentService extends AbstractService implements CommentServiceInterface
{
    public function __construct(
        private readonly CommentRepositoryInterface $repository,
        private readonly CommentMapper $mapper,
        private readonly CommentTransformer $transformer,
        private readonly ?DomainEventBusInterface $eventBus = null,
    ) {
    }

    public function index(CommentRequest $request): CommentResponse
    {
        $comments = array_map(
            fn (array $comment): array => $this->mapper->mapComment($comment)->toArray(),
            $this->repository->findComments(
                $request->limit(),
                $request->offset(),
                $request->movieId(),
                $request->reviewId(),
                $request->userId(),
                $request->parentId()
            )
        );

        return new CommentResponse([
            'items' => $comments,
            'pagination' => $this->pagination(
                $request,
                $this->repository->countComments(
                    $request->movieId(),
                    $request->reviewId(),
                    $request->userId(),
                    $request->parentId()
                )
            ),
        ]);
    }

    public function view(int $commentId): CommentResponse
    {
        return new CommentResponse([
            'comment' => $this->mapper->mapComment($this->findCommentOrFail($commentId))->toArray(),
        ]);
    }

    public function create(CommentRequest $request, int $userId): CommentResponse
    {
        $attributes = $request->commentAttributes();
        $this->validateTarget($request->movieId(), $request->reviewId());

        $comment = $this->repository->createComment($userId, $attributes);
        $extraPayload = [];

        if (($comment['review_user_id'] ?? null) !== null) {
            $extraPayload['recipient_user_id'] = (int) $comment['review_user_id'];
        }

        $this->publishCommentEvent(CommentEvent::CREATED, $comment, $extraPayload);

        return new CommentResponse([
            'comment' => $this->mapper->mapComment($comment)->toArray(),
        ]);
    }

    public function reply(int $parentId, CommentRequest $request, int $userId): CommentResponse
    {
        $parent = $this->findCommentOrFail($parentId);
        $attributes = [
            'body' => $request->commentAttributes()['body'] ?? null,
            'parent_id' => $parentId,
            'movie_id' => $parent['movie_id'] !== null ? (int) $parent['movie_id'] : null,
            'review_id' => $parent['review_id'] !== null ? (int) $parent['review_id'] : null,
        ];

        $comment = $this->repository->createComment($userId, $attributes);
        $this->publishCommentEvent(CommentEvent::REPLIED, $comment, [
            'parent_comment_id' => $parentId,
            'recipient_user_id' => (int) $parent['user_id'],
        ]);

        return new CommentResponse([
            'comment' => $this->mapper->mapComment($comment)->toArray(),
        ]);
    }

    public function update(int $commentId, CommentRequest $request, int $userId): CommentResponse
    {
        $comment = $this->findOwnedCommentOrFail($commentId, $userId);
        $attributes = $request->commentAttributes();

        if (!array_key_exists('body', $attributes)) {
            throw new CommentException(
                'Comment body must be provided',
                422,
                CommentException::CODE_VALIDATION_ERROR
            );
        }

        $this->repository->updateComment($commentId, $userId, ['body' => $attributes['body']]);
        $updatedComment = $this->repository->findOwnedComment($commentId, $userId) ?? $comment;

        return new CommentResponse([
            'comment' => $this->mapper->mapComment($updatedComment)->toArray(),
        ]);
    }

    public function delete(int $commentId, int $userId): CommentResponse
    {
        $this->findOwnedCommentOrFail($commentId, $userId);
        $this->repository->softDeleteComment($commentId, $userId);

        return new CommentResponse($this->transformer->deletedPayload($commentId));
    }

    public function like(int $commentId, int $userId): CommentResponse
    {
        $target = $this->findCommentOrFail($commentId);
        $comment = $this->repository->likeComment($commentId, $userId);
        $this->publishCommentEvent(CommentEvent::LIKED, $comment, [
            'recipient_user_id' => (int) $target['user_id'],
        ], $userId);

        return new CommentResponse($this->transformer->likedPayload($commentId, (int) $comment['likes_count']));
    }

    /**
     * @return array<string, mixed>
     */
    private function findCommentOrFail(int $commentId): array
    {
        $comment = $this->repository->findComment($commentId);

        if ($comment === null) {
            throw new CommentException('Comment not found', 404, CommentException::CODE_COMMENT_NOT_FOUND);
        }

        return $comment;
    }

    /**
     * @return array<string, mixed>
     */
    private function findOwnedCommentOrFail(int $commentId, int $userId): array
    {
        $comment = $this->repository->findOwnedComment($commentId, $userId);

        if ($comment === null) {
            throw new CommentException('Comment not found', 404, CommentException::CODE_COMMENT_NOT_FOUND);
        }

        return $comment;
    }

    private function validateTarget(?int $movieId, ?int $reviewId): void
    {
        if (($movieId === null && $reviewId === null) || ($movieId !== null && $reviewId !== null)) {
            throw new CommentException(
                'Comment must target exactly one movie or review',
                422,
                CommentException::CODE_VALIDATION_ERROR
            );
        }

        if ($movieId !== null && !$this->repository->movieExists($movieId)) {
            throw new CommentException('Movie not found', 404, CommentException::CODE_MOVIE_NOT_FOUND);
        }

        if ($reviewId !== null && !$this->repository->reviewExists($reviewId)) {
            throw new CommentException('Review not found', 404, CommentException::CODE_REVIEW_NOT_FOUND);
        }
    }

    /**
     * @return array<string, int|bool>
     */
    private function pagination(CommentRequest $request, int $total): array
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
     * @param array<string, mixed> $comment
     * @param array<string, mixed> $extraPayload
     */
    private function publishCommentEvent(
        string $eventName,
        array $comment,
        array $extraPayload = [],
        ?int $actorId = null
    ): void {
        if ($this->eventBus === null) {
            return;
        }

        $movieId = $comment['movie_id'] ?? $comment['review_movie_id'] ?? null;
        $movieSlug = $comment['movie_slug'] ?? $comment['review_movie_slug'] ?? null;
        $movieTitle = $comment['movie_title'] ?? $comment['review_movie_title'] ?? null;
        $moviePosterUrl = $comment['movie_poster_url'] ?? $comment['review_movie_poster_url'] ?? null;

        $payload = [
            'comment_id' => (int) $comment['id'],
            'parent_comment_id' => $comment['parent_id'] !== null ? (int) $comment['parent_id'] : null,
            'movie_id' => $movieId !== null ? (int) $movieId : null,
            'movie_slug' => $movieSlug !== null ? (string) $movieSlug : null,
            'movie_title' => $movieTitle !== null ? (string) $movieTitle : null,
            'movie_poster_url' => $moviePosterUrl !== null ? (string) $moviePosterUrl : null,
            'review_id' => $comment['review_id'] !== null ? (int) $comment['review_id'] : null,
            'review_title' => $comment['review_title'] !== null ? (string) $comment['review_title'] : null,
            'body_excerpt' => $this->excerpt((string) $comment['body']),
            'likes_count' => (int) $comment['likes_count'],
        ];

        $this->eventBus->publish(new CommentEvent(
            $eventName,
            $actorId ?? (int) $comment['user_id'],
            (int) $comment['id'],
            array_merge($payload, $extraPayload),
        ));
    }

    private function excerpt(string $body): string
    {
        $body = trim($body);

        return mb_substr($body, 0, 160);
    }
}
