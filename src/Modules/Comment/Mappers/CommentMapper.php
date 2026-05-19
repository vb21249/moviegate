<?php

declare(strict_types=1);

namespace App\Modules\Comment\Mappers;

use App\Modules\Comment\DTO\CommentDto;

/**
 * Maps persistence rows into comment DTOs.
 */
final class CommentMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public function mapComment(array $row): CommentDto
    {
        $movieId = $row['movie_id'] !== null ? (int) $row['movie_id'] : null;
        $reviewId = $row['review_id'] !== null ? (int) $row['review_id'] : null;

        return new CommentDto(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            movieId: $movieId,
            reviewId: $reviewId,
            parentId: $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
            body: (string) $row['body'],
            likesCount: (int) $row['likes_count'],
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
            user: [
                'id' => (int) $row['user_id'],
                'username' => $row['username'] !== null ? (string) $row['username'] : null,
                'avatar_url' => $row['user_avatar_url'] !== null ? (string) $row['user_avatar_url'] : null,
            ],
            movie: $movieId !== null ? [
                'id' => $movieId,
                'slug' => $row['movie_slug'] !== null ? (string) $row['movie_slug'] : null,
                'title' => $row['movie_title'] !== null ? (string) $row['movie_title'] : null,
                'poster_url' => $row['movie_poster_url'] !== null ? (string) $row['movie_poster_url'] : null,
                'release_date' => $row['movie_release_date'] !== null ? (string) $row['movie_release_date'] : null,
            ] : null,
            review: $reviewId !== null ? [
                'id' => $reviewId,
                'movie_id' => $row['review_movie_id'] !== null ? (int) $row['review_movie_id'] : null,
                'title' => $row['review_title'] !== null ? (string) $row['review_title'] : null,
            ] : null,
        );
    }
}
