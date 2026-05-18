<?php

declare(strict_types=1);

namespace App\Modules\User\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\User\Enums\UserStatus;
use App\Modules\User\Interfaces\UserRepositoryInterface;
use yii\db\Expression;
use yii\db\Query;

/**
 * User repository backed by users and user-related projection tables.
 */
final class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    private const USERS_TABLE = '{{%users}}';
    private const FOLLOWS_TABLE = '{{%follows}}';
    private const RATINGS_TABLE = '{{%ratings}}';
    private const REVIEWS_TABLE = '{{%reviews}}';
    private const COMMENTS_TABLE = '{{%comments}}';
    private const FEED_EVENTS_TABLE = '{{%feed_events}}';
    private const MOVIES_TABLE = '{{%movies}}';

    public function findPublicProfiles(int $limit, int $offset): array
    {
        return (new Query())
            ->select([
                'id' => 'u.id',
                'username' => 'u.username',
                'avatar_url' => 'u.avatar_url',
                'bio' => 'u.bio',
                'status' => 'u.status',
                'created_at' => 'u.created_at',
            ])
            ->from(['u' => self::USERS_TABLE])
            ->andWhere(['u.status' => UserStatus::Active->value])
            ->andWhere(['u.deleted_at' => null])
            ->orderBy(['u.created_at' => SORT_DESC, 'u.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countPublicProfiles(): int
    {
        return $this->countRows(self::USERS_TABLE, [
            'status' => UserStatus::Active->value,
            'deleted_at' => null,
        ]);
    }

    public function findPublicProfile(int $userId): ?array
    {
        $profile = (new Query())
            ->select([
                'id' => 'u.id',
                'username' => 'u.username',
                'avatar_url' => 'u.avatar_url',
                'bio' => 'u.bio',
                'status' => 'u.status',
                'created_at' => 'u.created_at',
            ])
            ->from(['u' => self::USERS_TABLE])
            ->andWhere(['u.id' => $userId])
            ->andWhere(['u.status' => UserStatus::Active->value])
            ->andWhere(['u.deleted_at' => null])
            ->one($this->db());

        return $profile === false ? null : $profile;
    }

    public function getProfileStats(int $userId): array
    {
        return [
            'followers_count' => $this->countRows(self::FOLLOWS_TABLE, ['followed_id' => $userId]),
            'following_count' => $this->countRows(self::FOLLOWS_TABLE, ['follower_id' => $userId]),
            'ratings_count' => $this->countRows(self::RATINGS_TABLE, [
                'user_id' => $userId,
                'deleted_at' => null,
            ]),
            'reviews_count' => $this->countRows(self::REVIEWS_TABLE, [
                'user_id' => $userId,
                'status' => 'published',
                'deleted_at' => null,
            ]),
            'comments_count' => $this->countRows(self::COMMENTS_TABLE, [
                'user_id' => $userId,
                'deleted_at' => null,
            ]),
        ];
    }

    public function findActivity(int $userId, int $limit, int $offset): array
    {
        return (new Query())
            ->select([
                'id' => 'fe.id',
                'event_type' => 'fe.event_type',
                'entity_type' => 'fe.entity_type',
                'entity_id' => 'fe.entity_id',
                'payload_json' => 'fe.payload_json',
                'occurred_at' => 'fe.occurred_at',
            ])
            ->from(['fe' => self::FEED_EVENTS_TABLE])
            ->andWhere(['fe.user_id' => $userId])
            ->orderBy(['fe.occurred_at' => SORT_DESC, 'fe.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countActivity(int $userId): int
    {
        return $this->countRows(self::FEED_EVENTS_TABLE, ['user_id' => $userId]);
    }

    public function findRecentRatings(int $userId, int $limit, int $offset): array
    {
        return (new Query())
            ->select([
                'id' => 'r.id',
                'score' => 'r.score',
                'review_text' => 'r.review_text',
                'rated_at' => 'r.rated_at',
                'movie_id' => 'm.id',
                'movie_slug' => 'm.slug',
                'movie_title' => 'm.title',
                'movie_poster_url' => 'm.poster_url',
                'movie_release_date' => 'm.release_date',
            ])
            ->from(['r' => self::RATINGS_TABLE])
            ->leftJoin(['m' => self::MOVIES_TABLE], 'm.id = r.movie_id AND m.deleted_at IS NULL')
            ->andWhere(['r.user_id' => $userId])
            ->andWhere(['r.deleted_at' => null])
            ->orderBy(['r.rated_at' => SORT_DESC, 'r.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countRecentRatings(int $userId): int
    {
        return $this->countRows(self::RATINGS_TABLE, [
            'user_id' => $userId,
            'deleted_at' => null,
        ]);
    }

    public function findPosts(int $userId, int $limit, int $offset): array
    {
        return (new Query())
            ->select([
                'id' => 'r.id',
                'title' => 'r.title',
                'body' => 'r.body',
                'likes_count' => 'r.likes_count',
                'published_at' => 'r.published_at',
                'created_at' => 'r.created_at',
                'movie_id' => 'm.id',
                'movie_slug' => 'm.slug',
                'movie_title' => 'm.title',
                'movie_poster_url' => 'm.poster_url',
                'movie_release_date' => 'm.release_date',
            ])
            ->from(['r' => self::REVIEWS_TABLE])
            ->leftJoin(['m' => self::MOVIES_TABLE], 'm.id = r.movie_id AND m.deleted_at IS NULL')
            ->andWhere(['r.user_id' => $userId])
            ->andWhere(['r.status' => 'published'])
            ->andWhere(['r.deleted_at' => null])
            ->orderBy(new Expression('COALESCE(r.published_at, r.created_at) DESC, r.id DESC'))
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countPosts(int $userId): int
    {
        return $this->countRows(self::REVIEWS_TABLE, [
            'user_id' => $userId,
            'status' => 'published',
            'deleted_at' => null,
        ]);
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
