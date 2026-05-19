<?php

declare(strict_types=1);

namespace App\Modules\Review\Mappers;

use App\Modules\Review\DTO\ReviewDto;

/**
 * Maps persistence rows into review DTOs.
 */
final class ReviewMapper
{
    /**
     * @param array<string, mixed> $row
     *
     * @return ReviewDto
     */
    public function mapReview(array $row): ReviewDto
    {
        return new ReviewDto(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            movieId: (int) $row['movie_id'],
            ratingId: $row['rating_id'] !== null ? (int) $row['rating_id'] : null,
            title: (string) $row['title'],
            body: (string) $row['body'],
            likesCount: (int) $row['likes_count'],
            status: (string) $row['status'],
            publishedAt: $row['published_at'] !== null ? (string) $row['published_at'] : null,
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
