<?php

declare(strict_types=1);

namespace App\Modules\Rating\Mappers;

use App\Modules\Rating\DTO\RatingDto;

/**
 * Maps persistence rows into rating DTOs.
 */
final class RatingMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public function mapRating(array $row): RatingDto
    {
        return new RatingDto(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            movieId: (int) $row['movie_id'],
            score: (int) $row['score'],
            reviewText: $row['review_text'] !== null ? (string) $row['review_text'] : null,
            ratedAt: $row['rated_at'] !== null ? (string) $row['rated_at'] : null,
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
            user: [
                'id' => (int) $row['user_id'],
                'username' => $row['username'] !== null ? (string) $row['username'] : null,
                'avatar_url' => $row['user_avatar_url'] !== null ? (string) $row['user_avatar_url'] : null,
            ],
            movie: [
                'id' => (int) $row['movie_id'],
                'slug' => $row['movie_slug'] !== null ? (string) $row['movie_slug'] : null,
                'title' => $row['movie_title'] !== null ? (string) $row['movie_title'] : null,
                'poster_url' => $row['movie_poster_url'] !== null ? (string) $row['movie_poster_url'] : null,
                'release_date' => $row['movie_release_date'] !== null ? (string) $row['movie_release_date'] : null,
            ],
        );
    }
}
