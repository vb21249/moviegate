<?php

declare(strict_types=1);

namespace App\Modules\Review\DTO;

/**
 * Public review DTO.
 */
final class ReviewDto
{
    /**
     * @param int $id
     * @param int $userId
     * @param int $movieId
     * @param int|null $ratingId
     * @param string $title
     * @param string $body
     * @param int $likesCount
     * @param string $status
     * @param string|null $publishedAt
     * @param string|null $createdAt
     * @param string|null $updatedAt
     * @param array<string, mixed>|null $user
     * @param array<string, mixed>|null $movie
     */
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly int $movieId,
        public readonly ?int $ratingId,
        public readonly string $title,
        public readonly string $body,
        public readonly int $likesCount,
        public readonly string $status,
        public readonly ?string $publishedAt,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?array $user = null,
        public readonly ?array $movie = null,
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
            'rating_id' => $this->ratingId,
            'title' => $this->title,
            'body' => $this->body,
            'likes_count' => $this->likesCount,
            'status' => $this->status,
            'published_at' => $this->publishedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'user' => $this->user,
            'movie' => $this->movie,
        ];
    }
}
