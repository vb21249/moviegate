<?php

declare(strict_types=1);

namespace App\Modules\Comment\DTO;

/**
 * Public comment DTO.
 */
final class CommentDto
{
    /**
     * @param array<string, mixed>|null $user
     * @param array<string, mixed>|null $movie
     * @param array<string, mixed>|null $review
     */
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly ?int $movieId,
        public readonly ?int $reviewId,
        public readonly ?int $parentId,
        public readonly string $body,
        public readonly int $likesCount,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?array $user = null,
        public readonly ?array $movie = null,
        public readonly ?array $review = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'movie_id' => $this->movieId,
            'review_id' => $this->reviewId,
            'parent_id' => $this->parentId,
            'body' => $this->body,
            'likes_count' => $this->likesCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'user' => $this->user,
            'movie' => $this->movie,
            'review' => $this->review,
        ];
    }
}
