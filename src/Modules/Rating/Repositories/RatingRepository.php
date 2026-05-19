<?php

declare(strict_types=1);

namespace App\Modules\Rating\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Movie\Enums\MovieStatus;
use App\Modules\Rating\Exceptions\RatingException;
use App\Modules\Rating\Interfaces\RatingRepositoryInterface;
use yii\db\Expression;
use yii\db\Query;

/**
 * Rating repository backed by ratings and related projection tables.
 */
final class RatingRepository extends BaseRepository implements RatingRepositoryInterface
{
    private const RATINGS_TABLE = '{{%ratings}}';
    private const MOVIES_TABLE = '{{%movies}}';
    private const USERS_TABLE = '{{%users}}';

    public function findRatings(
        int $limit,
        int $offset,
        ?int $movieId = null,
        ?int $userId = null,
        ?int $score = null
    ): array {
        return $this->publicRatingQuery($movieId, $userId, $score)
            ->orderBy(['r.rated_at' => SORT_DESC, 'r.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countRatings(?int $movieId = null, ?int $userId = null, ?int $score = null): int
    {
        return (int) $this->publicRatingQuery($movieId, $userId, $score)
            ->count('*', $this->db());
    }

    public function findRating(int $ratingId): ?array
    {
        $rating = $this->publicRatingQuery()
            ->andWhere(['r.id' => $ratingId])
            ->one($this->db());

        return $rating === false ? null : $rating;
    }

    public function findOwnedRating(int $ratingId, int $userId): ?array
    {
        $rating = $this->ratingQuery()
            ->andWhere([
                'r.id' => $ratingId,
                'r.user_id' => $userId,
                'r.deleted_at' => null,
            ])
            ->one($this->db());

        return $rating === false ? null : $rating;
    }

    public function findOwnedRatingForMovie(int $userId, int $movieId): ?array
    {
        $rating = $this->ratingQuery()
            ->andWhere([
                'r.user_id' => $userId,
                'r.movie_id' => $movieId,
                'r.deleted_at' => null,
            ])
            ->orderBy(['r.rated_at' => SORT_DESC, 'r.id' => SORT_DESC])
            ->one($this->db());

        return $rating === false ? null : $rating;
    }

    public function movieExists(int $movieId): bool
    {
        return (new Query())
            ->from(self::MOVIES_TABLE)
            ->andWhere([
                'id' => $movieId,
                'status' => MovieStatus::Active->value,
                'deleted_at' => null,
            ])
            ->exists($this->db());
    }

    public function createRating(int $userId, array $attributes): array
    {
        $now = new Expression('NOW()');
        $this->db()->createCommand()->insert(self::RATINGS_TABLE, [
            'user_id' => $userId,
            'movie_id' => $attributes['movie_id'],
            'score' => $attributes['score'],
            'review_text' => $attributes['review_text'] ?? null,
            'rated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $userId,
            'updated_by' => $userId,
        ])->execute();

        $ratingId = (int) $this->db()->getLastInsertID();
        $rating = $this->findOwnedRating($ratingId, $userId);

        if ($rating === null) {
            throw new RatingException(
                'Rating was created but could not be loaded',
                500,
                RatingException::CODE_RATING_CREATE_FAILED
            );
        }

        return $rating;
    }

    public function updateRating(int $ratingId, int $userId, array $attributes): void
    {
        $attributes['rated_at'] = new Expression('NOW()');
        $attributes['updated_at'] = new Expression('NOW()');
        $attributes['updated_by'] = $userId;

        $this->db()->createCommand()
            ->update(self::RATINGS_TABLE, $attributes, [
                'id' => $ratingId,
                'user_id' => $userId,
                'deleted_at' => null,
            ])
            ->execute();
    }

    public function softDeleteRating(int $ratingId, int $userId): void
    {
        $now = new Expression('NOW()');
        $this->db()->createCommand()
            ->update(self::RATINGS_TABLE, [
                'deleted_at' => $now,
                'deleted_by' => $userId,
                'updated_at' => $now,
                'updated_by' => $userId,
            ], [
                'id' => $ratingId,
                'user_id' => $userId,
                'deleted_at' => null,
            ])
            ->execute();
    }

    public function findUserHistory(int $userId, int $limit, int $offset, ?int $movieId = null): array
    {
        return $this->publicRatingQuery($movieId, $userId)
            ->orderBy(['r.rated_at' => SORT_DESC, 'r.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countUserHistory(int $userId, ?int $movieId = null): int
    {
        return (int) $this->publicRatingQuery($movieId, $userId)
            ->count('*', $this->db());
    }

    private function publicRatingQuery(?int $movieId = null, ?int $userId = null, ?int $score = null): Query
    {
        return $this->ratingQuery()
            ->andWhere(['r.deleted_at' => null])
            ->andWhere(['m.status' => MovieStatus::Active->value])
            ->andWhere(['m.deleted_at' => null])
            ->andWhere(['u.status' => 'active'])
            ->andWhere(['u.deleted_at' => null])
            ->andFilterWhere(['r.movie_id' => $movieId])
            ->andFilterWhere(['r.user_id' => $userId])
            ->andFilterWhere(['r.score' => $score]);
    }

    private function ratingQuery(): Query
    {
        return (new Query())
            ->select([
                'id' => 'r.id',
                'user_id' => 'r.user_id',
                'movie_id' => 'r.movie_id',
                'score' => 'r.score',
                'review_text' => 'r.review_text',
                'rated_at' => 'r.rated_at',
                'created_at' => 'r.created_at',
                'updated_at' => 'r.updated_at',
                'username' => 'u.username',
                'user_avatar_url' => 'u.avatar_url',
                'movie_slug' => 'm.slug',
                'movie_title' => 'm.title',
                'movie_poster_url' => 'm.poster_url',
                'movie_release_date' => 'm.release_date',
            ])
            ->from(['r' => self::RATINGS_TABLE])
            ->leftJoin(['u' => self::USERS_TABLE], 'u.id = r.user_id')
            ->leftJoin(['m' => self::MOVIES_TABLE], 'm.id = r.movie_id');
    }
}
