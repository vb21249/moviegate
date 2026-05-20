<?php

declare(strict_types=1);

namespace App\Modules\Movie\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Movie\Enums\MovieStatus;
use App\Modules\Movie\Interfaces\MovieRepositoryInterface;
use RuntimeException;
use yii\db\Expression;
use yii\db\Query;

/**
 * Movie repository backed by catalog and aggregate source tables.
 */
final class MovieRepository extends BaseRepository implements MovieRepositoryInterface
{
    private const MOVIES_TABLE = '{{%movies}}';
    private const RATINGS_TABLE = '{{%ratings}}';
    private const REVIEWS_TABLE = '{{%reviews}}';
    private const COMMENTS_TABLE = '{{%comments}}';
    private const MOVIE_VIEWS_TABLE = '{{%movie_views}}';

    public function findPublishedMovies(int $limit, int $offset, ?string $query = null): array
    {
        return $this->baseMovieQuery($query)
            ->orderBy(['m.created_at' => SORT_DESC, 'm.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countPublishedMovies(?string $query = null): int
    {
        return (int) $this->baseMovieQuery($query)
            ->count('*', $this->db());
    }

    public function findPublishedMovie(int $movieId): ?array
    {
        $movie = $this->baseMovieQuery()
            ->andWhere(['m.id' => $movieId])
            ->one($this->db());

        return $movie === false ? null : $movie;
    }

    public function getMovieStats(int $movieId): array
    {
        $ratingStats = (new Query())
            ->select([
                'ratings_count' => new Expression('COUNT(*)'),
                'average_rating' => new Expression('AVG(score)'),
            ])
            ->from(self::RATINGS_TABLE)
            ->andWhere(['movie_id' => $movieId])
            ->andWhere(['deleted_at' => null])
            ->one($this->db());

        $averageRating = $ratingStats['average_rating'] ?? null;

        return [
            'ratings_count' => (int) ($ratingStats['ratings_count'] ?? 0),
            'average_rating' => $averageRating !== null ? round((float) $averageRating, 2) : null,
            'reviews_count' => $this->countRows(self::REVIEWS_TABLE, [
                'movie_id' => $movieId,
                'status' => 'published',
                'deleted_at' => null,
            ]),
            'comments_count' => $this->countRows(self::COMMENTS_TABLE, [
                'movie_id' => $movieId,
                'deleted_at' => null,
            ]),
            'views_count' => $this->countRows(self::MOVIE_VIEWS_TABLE, ['movie_id' => $movieId]),
        ];
    }

    public function upsertImportedMovie(array $movie): array
    {
        $tmdbId = (int) $movie['tmdb_id'];
        $now = new Expression('NOW()');
        $columns = [
            'tmdb_id' => $tmdbId,
            'slug' => (string) $movie['slug'],
            'title' => (string) $movie['title'],
            'original_title' => $movie['original_title'] ?? null,
            'overview' => $movie['overview'] ?? null,
            'poster_url' => $movie['poster_url'] ?? null,
            'backdrop_url' => $movie['backdrop_url'] ?? null,
            'release_date' => $movie['release_date'] ?? null,
            'runtime_minutes' => $movie['runtime_minutes'] ?? null,
            'status' => $movie['status'] ?? MovieStatus::Active->value,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $updates = $columns;
        unset($updates['tmdb_id'], $updates['created_at']);
        $updates['updated_at'] = $now;

        $this->db()
            ->createCommand()
            ->upsert(self::MOVIES_TABLE, $columns, $updates)
            ->execute();

        $record = (new Query())
            ->select([
                'id',
                'tmdb_id',
                'slug',
                'title',
                'original_title',
                'overview',
                'poster_url',
                'backdrop_url',
                'release_date',
                'runtime_minutes',
                'status',
                'created_at',
                'updated_at',
            ])
            ->from(self::MOVIES_TABLE)
            ->where(['tmdb_id' => $tmdbId])
            ->one($this->db());

        if ($record === false) {
            throw new RuntimeException('Imported movie was not found after upsert.');
        }

        return $record;
    }

    public function markWatched(int $movieId, int $userId): void
    {
        $this->db()->createCommand()->upsert(
            self::MOVIE_VIEWS_TABLE,
            [
                'movie_id' => $movieId,
                'user_id' => $userId,
                'viewed_at' => new Expression('NOW()'),
                'created_at' => new Expression('NOW()'),
            ],
            [
                'viewed_at' => new Expression('NOW()'),
            ]
        )->execute();
    }

    /**
     * @param string|null $query
     *
     * @return Query
     */
    private function baseMovieQuery(?string $query = null): Query
    {
        $movieQuery = (new Query())
            ->select([
                'id' => 'm.id',
                'tmdb_id' => 'm.tmdb_id',
                'slug' => 'm.slug',
                'title' => 'm.title',
                'original_title' => 'm.original_title',
                'overview' => 'm.overview',
                'poster_url' => 'm.poster_url',
                'backdrop_url' => 'm.backdrop_url',
                'release_date' => 'm.release_date',
                'runtime_minutes' => 'm.runtime_minutes',
                'status' => 'm.status',
                'created_at' => 'm.created_at',
                'updated_at' => 'm.updated_at',
            ])
            ->from(['m' => self::MOVIES_TABLE])
            ->andWhere(['m.status' => MovieStatus::Active->value])
            ->andWhere(['m.deleted_at' => null]);

        $query = $query !== null ? trim($query) : '';

        if ($query !== '') {
            $movieQuery->andWhere([
                'or',
                ['like', 'm.title', $query],
                ['like', 'm.original_title', $query],
                ['like', 'm.slug', $query],
            ]);
        }

        return $movieQuery;
    }

    /**
     * @param string $table
     * @param array<string, mixed> $where
     *
     * @return int
     */
    private function countRows(string $table, array $where): int
    {
        return (int) (new Query())
            ->from($table)
            ->andWhere($where)
            ->count('*', $this->db());
    }
}
