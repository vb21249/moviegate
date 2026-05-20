<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Movie\Enums\MovieStatus;
use App\Modules\Recommendation\Interfaces\RecommendationRepositoryInterface;
use App\Modules\Review\Enums\ReviewStatus;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Json;

/**
 * Recommendation repository backed by recommendation_cache and catalog projections.
 */
final class RecommendationRepository extends BaseRepository implements RecommendationRepositoryInterface
{
    private const RECOMMENDATION_CACHE_TABLE = '{{%recommendation_cache}}';
    private const MOVIES_TABLE = '{{%movies}}';
    private const RATINGS_TABLE = '{{%ratings}}';
    private const REVIEWS_TABLE = '{{%reviews}}';
    private const MOVIE_VIEWS_TABLE = '{{%movie_views}}';
    private const PLAYLISTS_TABLE = '{{%playlists}}';
    private const PLAYLIST_MOVIES_TABLE = '{{%playlist_movies}}';

    public function findPopularMovies(int $limit, ?int $excludeUserId = null): array
    {
        return $this->movieRecommendationQuery($this->interactedMovieIds($excludeUserId))
            ->addSelect([
                'similar_users_count' => new Expression('0'),
                'recommendation_score' => new Expression(
                    'COALESCE(rs.average_rating, 0) * 8 '
                    . '+ COALESCE(rs.ratings_count, 0) * 2 '
                    . '+ COALESCE(vs.views_count, 0) '
                    . '+ COALESCE(rvs.reviews_count, 0) * 1.5 '
                    . '+ CASE WHEN m.poster_url IS NOT NULL AND m.poster_url <> "" THEN 2 ELSE 0 END'
                ),
            ])
            ->orderBy([
                'recommendation_score' => SORT_DESC,
                'average_rating' => SORT_DESC,
                'ratings_count' => SORT_DESC,
                'm.created_at' => SORT_DESC,
                'm.id' => SORT_DESC,
            ])
            ->limit($limit)
            ->all($this->db());
    }

    public function findRecentMovies(int $limit, ?int $excludeUserId = null): array
    {
        return $this->movieRecommendationQuery($this->interactedMovieIds($excludeUserId))
            ->addSelect([
                'similar_users_count' => new Expression('0'),
                'recommendation_score' => new Expression(
                    'COALESCE(rs.average_rating, 0) * 3 '
                    . '+ COALESCE(rs.ratings_count, 0) '
                    . '+ CASE WHEN m.poster_url IS NOT NULL AND m.poster_url <> "" THEN 2 ELSE 0 END'
                ),
            ])
            ->orderBy([
                'm.created_at' => SORT_DESC,
                'm.id' => SORT_DESC,
            ])
            ->limit($limit)
            ->all($this->db());
    }

    public function findPersonalizedMovies(int $userId, int $limit): array
    {
        $excludeMovieIds = $this->interactedMovieIds($userId);
        $likedMovieIds = $this->likedMovieIds($userId);
        $query = $this->movieRecommendationQuery($excludeMovieIds);

        if ($likedMovieIds !== []) {
            $query->leftJoin(['ss' => $this->similarMovieStatsQuery($userId, $likedMovieIds)], 'ss.movie_id = m.id');
            $similarUsersCount = 'COALESCE(ss.similar_users_count, 0)';
            $similarAverageRating = 'COALESCE(ss.similar_average_rating, 0)';
        } else {
            $similarUsersCount = '0';
            $similarAverageRating = '0';
        }

        return $query
            ->addSelect([
                'similar_users_count' => new Expression($similarUsersCount),
                'recommendation_score' => new Expression(
                    $similarUsersCount . ' * 25 '
                    . '+ ' . $similarAverageRating . ' * 6 '
                    . '+ COALESCE(rs.average_rating, 0) * 6 '
                    . '+ COALESCE(rs.ratings_count, 0) * 1.5 '
                    . '+ COALESCE(vs.views_count, 0) '
                    . '+ COALESCE(rvs.reviews_count, 0) '
                    . '+ CASE WHEN m.poster_url IS NOT NULL AND m.poster_url <> "" THEN 2 ELSE 0 END'
                ),
            ])
            ->orderBy([
                'recommendation_score' => SORT_DESC,
                'similar_users_count' => SORT_DESC,
                'average_rating' => SORT_DESC,
                'ratings_count' => SORT_DESC,
                'm.created_at' => SORT_DESC,
                'm.id' => SORT_DESC,
            ])
            ->limit($limit)
            ->all($this->db());
    }

    public function findFreshCache(int $userId, string $source): ?array
    {
        $row = (new Query())
            ->select([
                'id',
                'user_id',
                'source',
                'payload_json',
                'expires_at',
                'created_at',
                'updated_at',
            ])
            ->from(self::RECOMMENDATION_CACHE_TABLE)
            ->andWhere([
                'user_id' => $userId,
                'source' => $source,
            ])
            ->andWhere([
                'or',
                ['expires_at' => null],
                ['>', 'expires_at', new Expression('NOW()')],
            ])
            ->orderBy(['updated_at' => SORT_DESC, 'id' => SORT_DESC])
            ->one($this->db());

        return $row === false ? null : $this->decodeCachePayload($row);
    }

    public function storeCache(int $userId, string $source, array $payload, int $ttlSeconds): array
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $ttlSeconds);
        $transaction = $this->db()->beginTransaction();

        try {
            $this->db()->createCommand()
                ->delete(self::RECOMMENDATION_CACHE_TABLE, [
                    'user_id' => $userId,
                    'source' => $source,
                ])
                ->execute();

            $this->db()->createCommand()
                ->insert(self::RECOMMENDATION_CACHE_TABLE, [
                    'user_id' => $userId,
                    'source' => $source,
                    'payload_json' => Json::encode($payload),
                    'expires_at' => $expiresAt,
                    'created_at' => new Expression('NOW()'),
                    'updated_at' => new Expression('NOW()'),
                ])
                ->execute();

            $transaction->commit();
        } catch (\Throwable $throwable) {
            $transaction->rollBack();

            throw $throwable;
        }

        return $this->findFreshCache($userId, $source) ?? [
            'user_id' => $userId,
            'source' => $source,
            'payload_json' => $payload,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * @param list<int> $excludeMovieIds
     */
    private function movieRecommendationQuery(array $excludeMovieIds = []): Query
    {
        $query = (new Query())
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
                'ratings_count' => new Expression('COALESCE(rs.ratings_count, 0)'),
                'average_rating' => new Expression('rs.average_rating'),
                'reviews_count' => new Expression('COALESCE(rvs.reviews_count, 0)'),
                'views_count' => new Expression('COALESCE(vs.views_count, 0)'),
            ])
            ->from(['m' => self::MOVIES_TABLE])
            ->leftJoin(['rs' => $this->ratingStatsQuery()], 'rs.movie_id = m.id')
            ->leftJoin(['rvs' => $this->reviewStatsQuery()], 'rvs.movie_id = m.id')
            ->leftJoin(['vs' => $this->viewStatsQuery()], 'vs.movie_id = m.id')
            ->andWhere([
                'm.status' => MovieStatus::Active->value,
                'm.deleted_at' => null,
            ]);

        if ($excludeMovieIds !== []) {
            $query->andWhere(['not in', 'm.id', $excludeMovieIds]);
        }

        return $query;
    }

    private function ratingStatsQuery(): Query
    {
        return (new Query())
            ->select([
                'movie_id',
                'ratings_count' => new Expression('COUNT(*)'),
                'average_rating' => new Expression('AVG(score)'),
            ])
            ->from(self::RATINGS_TABLE)
            ->andWhere(['deleted_at' => null])
            ->groupBy('movie_id');
    }

    private function reviewStatsQuery(): Query
    {
        return (new Query())
            ->select([
                'movie_id',
                'reviews_count' => new Expression('COUNT(*)'),
            ])
            ->from(self::REVIEWS_TABLE)
            ->andWhere([
                'status' => ReviewStatus::Published->value,
                'deleted_at' => null,
            ])
            ->groupBy('movie_id');
    }

    private function viewStatsQuery(): Query
    {
        return (new Query())
            ->select([
                'movie_id',
                'views_count' => new Expression('COUNT(*)'),
            ])
            ->from(self::MOVIE_VIEWS_TABLE)
            ->groupBy('movie_id');
    }

    /**
     * @param list<int> $likedMovieIds
     */
    private function similarMovieStatsQuery(int $userId, array $likedMovieIds): Query
    {
        $similarUserIds = (new Query())
            ->select('user_id')
            ->from(self::RATINGS_TABLE)
            ->andWhere([
                'movie_id' => $likedMovieIds,
                'deleted_at' => null,
            ])
            ->andWhere(['>=', 'score', 7])
            ->andWhere(['<>', 'user_id', $userId])
            ->groupBy('user_id');

        return (new Query())
            ->select([
                'movie_id',
                'similar_users_count' => new Expression('COUNT(DISTINCT user_id)'),
                'similar_average_rating' => new Expression('AVG(score)'),
            ])
            ->from(self::RATINGS_TABLE)
            ->andWhere([
                'user_id' => $similarUserIds,
                'deleted_at' => null,
            ])
            ->andWhere(['>=', 'score', 7])
            ->groupBy('movie_id');
    }

    /**
     * @return list<int>
     */
    private function interactedMovieIds(?int $userId): array
    {
        if ($userId === null) {
            return [];
        }

        $movieIds = array_merge(
            $this->ratedMovieIds($userId),
            $this->viewedMovieIds($userId),
            $this->playlistMovieIds($userId),
        );

        return array_values(array_unique(array_map('intval', $movieIds)));
    }

    /**
     * @return list<int>
     */
    private function likedMovieIds(int $userId): array
    {
        return array_map('intval', (new Query())
            ->select('movie_id')
            ->from(self::RATINGS_TABLE)
            ->andWhere([
                'user_id' => $userId,
                'deleted_at' => null,
            ])
            ->andWhere(['>=', 'score', 7])
            ->column($this->db()));
    }

    /**
     * @return list<int>
     */
    private function ratedMovieIds(int $userId): array
    {
        return array_map('intval', (new Query())
            ->select('movie_id')
            ->from(self::RATINGS_TABLE)
            ->andWhere([
                'user_id' => $userId,
                'deleted_at' => null,
            ])
            ->column($this->db()));
    }

    /**
     * @return list<int>
     */
    private function viewedMovieIds(int $userId): array
    {
        return array_map('intval', (new Query())
            ->select('movie_id')
            ->from(self::MOVIE_VIEWS_TABLE)
            ->andWhere(['user_id' => $userId])
            ->column($this->db()));
    }

    /**
     * @return list<int>
     */
    private function playlistMovieIds(int $userId): array
    {
        return array_map('intval', (new Query())
            ->select('pm.movie_id')
            ->from(['pm' => self::PLAYLIST_MOVIES_TABLE])
            ->innerJoin(['p' => self::PLAYLISTS_TABLE], 'p.id = pm.playlist_id')
            ->andWhere([
                'p.user_id' => $userId,
                'p.deleted_at' => null,
                'pm.deleted_at' => null,
            ])
            ->column($this->db()));
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function decodeCachePayload(array $row): array
    {
        $payload = $row['payload_json'] ?? null;

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $row['payload_json'] = is_array($decoded) ? $decoded : [];
        }

        return $row;
    }
}
