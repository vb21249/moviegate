<?php

declare(strict_types=1);

namespace App\Modules\Comment\Services;

use App\Common\Services\AbstractService;
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

        return new CommentResponse([
            'comment' => $this->mapper->mapComment($this->repository->createComment($userId, $attributes))->toArray(),
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

        return new CommentResponse([
            'comment' => $this->mapper->mapComment($this->repository->createComment($userId, $attributes))->toArray(),
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
        $this->findCommentOrFail($commentId);
        $comment = $this->repository->likeComment($commentId, $userId);

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
}
