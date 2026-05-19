<?php

declare(strict_types=1);

namespace App\Modules\Movie\Transformers;

/**
 * Builds movie-specific API payload fragments.
 */
final class MovieTransformer
{
    /**
     * @param int $movieId
     * @param int $userId
     * @param array<string, mixed> $movie
     *
     * @return array<string, mixed>
     */
    public function watchedPayload(int $movieId, int $userId, array $movie): array
    {
        return [
            'message' => 'Movie marked as watched',
            'watched' => true,
            'movie_id' => $movieId,
            'user_id' => $userId,
            'movie' => [
                'id' => $movie['id'],
                'slug' => $movie['slug'],
                'title' => $movie['title'],
            ],
        ];
    }
}
