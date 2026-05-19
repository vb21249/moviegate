<?php

declare(strict_types=1);

namespace App\Modules\Rating\DTO;

/**
 * Public rating DTO.
 */
final class RatingDto
{
    /**
     * @param array<string, mixed>|null $user
     * @param array<string, mixed>|null $movie
     */
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly int $movieId,
        public readonly int $score,
        public readonly ?string $reviewText,
        public readonly ?string $ratedAt,
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
            'score' => $this->score,
            'review_text' => $this->reviewText,
            'rated_at' => $this->ratedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'user' => $this->user,
            'movie' => $this->movie,
        ];
    }
}
