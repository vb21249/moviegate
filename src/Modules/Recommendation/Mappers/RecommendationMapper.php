<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Mappers;

use App\Modules\Recommendation\DTO\RecommendationDto;

/**
 * Maps recommendation rows into public DTOs.
 */
final class RecommendationMapper
{
    /**
     * @param array<string, mixed> $row
     */
    public function mapRecommendation(array $row, string $source, int $rank): RecommendationDto
    {
        return new RecommendationDto(
            rank: $rank,
            source: $source,
            reason: $this->reason($row),
            score: round((float) ($row['recommendation_score'] ?? 0), 4),
            movie: [
                'id' => (int) $row['id'],
                'tmdb_id' => $row['tmdb_id'] !== null ? (int) $row['tmdb_id'] : null,
                'slug' => (string) $row['slug'],
                'title' => (string) $row['title'],
                'original_title' => $row['original_title'] !== null ? (string) $row['original_title'] : null,
                'overview' => $row['overview'] !== null ? (string) $row['overview'] : null,
                'poster_url' => $row['poster_url'] !== null ? (string) $row['poster_url'] : null,
                'backdrop_url' => $row['backdrop_url'] !== null ? (string) $row['backdrop_url'] : null,
                'release_date' => $row['release_date'] !== null ? (string) $row['release_date'] : null,
                'runtime_minutes' => $row['runtime_minutes'] !== null ? (int) $row['runtime_minutes'] : null,
                'status' => (string) $row['status'],
                'created_at' => $row['created_at'] !== null ? (string) $row['created_at'] : null,
                'updated_at' => $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
            ],
            stats: [
                'ratings_count' => (int) ($row['ratings_count'] ?? 0),
                'average_rating' => $row['average_rating'] !== null ? round((float) $row['average_rating'], 2) : null,
                'reviews_count' => (int) ($row['reviews_count'] ?? 0),
                'views_count' => (int) ($row['views_count'] ?? 0),
                'similar_users_count' => (int) ($row['similar_users_count'] ?? 0),
            ],
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function reason(array $row): string
    {
        if ((int) ($row['similar_users_count'] ?? 0) > 0) {
            return 'similar_users';
        }

        if ((int) ($row['ratings_count'] ?? 0) > 0 && (float) ($row['average_rating'] ?? 0) >= 7.0) {
            return 'highly_rated';
        }

        if ((int) ($row['views_count'] ?? 0) > 0) {
            return 'popular';
        }

        return 'recent_catalog';
    }
}
