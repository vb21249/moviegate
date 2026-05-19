<?php

declare(strict_types=1);

namespace App\Modules\Review\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Movie\Enums\MovieStatus;
use App\Modules\Review\Enums\ReviewStatus;
use App\Modules\Review\Exceptions\ReviewException;
use App\Modules\Review\Interfaces\ReviewRepositoryInterface;
use yii\db\Expression;
use yii\db\Query;

/**
 * Review repository backed by reviews and related projection tables.
 */
final class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    private const REVIEWS_TABLE = '{{%reviews}}';
    private const MOVIES_TABLE = '{{%movies}}';
    private const USERS_TABLE = '{{%users}}';
    private const RATINGS_TABLE = '{{%ratings}}';

    public function findPublishedReviews(
        int $limit,
        int $offset,
        ?string $query = null,
        ?int $movieId = null,
        ?int $userId = null
    ): array {
        return $this->baseReviewQuery($query, $movieId, $userId)
            ->orderBy(new Expression('COALESCE(r.published_at, r.created_at) DESC, r.id DESC'))
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countPublishedReviews(?string $query = null, ?int $movieId = null, ?int $userId = null): int
    {
        return (int) $this->baseReviewQuery($query, $movieId, $userId)
            ->count('*', $this->db());
    }

    public function findPublishedReview(int $reviewId): ?array
    {
        $review = $this->baseReviewQuery()
            ->andWhere(['r.id' => $reviewId])
            ->one($this->db());

        return $review === false ? null : $review;
    }

    public function findOwnedReview(int $reviewId, int $userId): ?array
    {
        $review = $this->reviewQuery()
            ->andWhere(['r.id' => $reviewId, 'r.user_id' => $userId])
            ->andWhere(['r.deleted_at' => null])
            ->one($this->db());

        return $review === false ? null : $review;
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

    public function ratingBelongsToUserMovie(int $ratingId, int $userId, int $movieId): bool
    {
        return (new Query())
            ->from(self::RATINGS_TABLE)
            ->andWhere([
                'id' => $ratingId,
                'user_id' => $userId,
                'movie_id' => $movieId,
                'deleted_at' => null,
            ])
            ->exists($this->db());
    }

    public function createReview(int $userId, array $attributes): array
    {
        $now = new Expression('NOW()');
        $status = $attributes['status'] ?? ReviewStatus::Published->value;
        $this->db()->createCommand()->insert(self::REVIEWS_TABLE, [
            'user_id' => $userId,
            'movie_id' => $attributes['movie_id'],
            'rating_id' => $attributes['rating_id'] ?? null,
            'title' => $attributes['title'],
            'body' => $attributes['body'],
            'status' => $status,
            'published_at' => $status === ReviewStatus::Published->value ? $now : null,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $userId,
            'updated_by' => $userId,
        ])->execute();

        $reviewId = (int) $this->db()->getLastInsertID();
        $review = $this->findOwnedReview($reviewId, $userId);

        if ($review === null) {
            throw new ReviewException(
                'Review was created but could not be loaded',
                500,
                ReviewException::CODE_REVIEW_CREATE_FAILED
            );
        }

        return $review;
    }

    public function updateReview(int $reviewId, int $userId, array $attributes): void
    {
        $attributes['updated_at'] = new Expression('NOW()');
        $attributes['updated_by'] = $userId;

        if (($attributes['status'] ?? null) === ReviewStatus::Published->value) {
            $attributes['published_at'] = new Expression('COALESCE(published_at, NOW())');
        }

        $this->db()->createCommand()
            ->update(self::REVIEWS_TABLE, $attributes, ['id' => $reviewId, 'user_id' => $userId])
            ->execute();
    }

    public function softDeleteReview(int $reviewId, int $userId): void
    {
        $now = new Expression('NOW()');
        $this->db()->createCommand()
            ->update(self::REVIEWS_TABLE, [
                'deleted_at' => $now,
                'deleted_by' => $userId,
                'updated_at' => $now,
                'updated_by' => $userId,
            ], [
                'id' => $reviewId,
                'user_id' => $userId,
                'deleted_at' => null,
            ])
            ->execute();
    }

    /**
     * @param string|null $query
     * @param int|null $movieId
     * @param int|null $userId
     *
     * @return Query
     */
    private function baseReviewQuery(?string $query = null, ?int $movieId = null, ?int $userId = null): Query
    {
        $reviewQuery = $this->reviewQuery()
            ->andWhere(['r.status' => ReviewStatus::Published->value])
            ->andWhere(['r.deleted_at' => null])
            ->andWhere(['m.status' => MovieStatus::Active->value])
            ->andWhere(['m.deleted_at' => null])
            ->andWhere(['u.status' => 'active'])
            ->andWhere(['u.deleted_at' => null])
            ->andFilterWhere(['r.movie_id' => $movieId])
            ->andFilterWhere(['r.user_id' => $userId]);

        $query = $query !== null ? trim($query) : '';

        if ($query !== '') {
            $reviewQuery->andWhere([
                'or',
                ['like', 'r.title', $query],
                ['like', 'r.body', $query],
                ['like', 'm.title', $query],
                ['like', 'u.username', $query],
            ]);
        }

        return $reviewQuery;
    }

    /**
     * @return Query
     */
    private function reviewQuery(): Query
    {
        return (new Query())
            ->select([
                'id' => 'r.id',
                'user_id' => 'r.user_id',
                'movie_id' => 'r.movie_id',
                'rating_id' => 'r.rating_id',
                'title' => 'r.title',
                'body' => 'r.body',
                'likes_count' => 'r.likes_count',
                'status' => 'r.status',
                'published_at' => 'r.published_at',
                'created_at' => 'r.created_at',
                'updated_at' => 'r.updated_at',
                'username' => 'u.username',
                'user_avatar_url' => 'u.avatar_url',
                'movie_slug' => 'm.slug',
                'movie_title' => 'm.title',
                'movie_poster_url' => 'm.poster_url',
                'movie_release_date' => 'm.release_date',
            ])
            ->from(['r' => self::REVIEWS_TABLE])
            ->leftJoin(['u' => self::USERS_TABLE], 'u.id = r.user_id')
            ->leftJoin(['m' => self::MOVIES_TABLE], 'm.id = r.movie_id');
    }
}
