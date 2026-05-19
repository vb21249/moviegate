<?php

declare(strict_types=1);

namespace App\Modules\Comment\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Comment\Exceptions\CommentException;
use App\Modules\Comment\Interfaces\CommentRepositoryInterface;
use App\Modules\Movie\Enums\MovieStatus;
use App\Modules\Review\Enums\ReviewStatus;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\Query;

/**
 * Comment repository backed by comments and comment_likes tables.
 */
final class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
    private const COMMENTS_TABLE = '{{%comments}}';
    private const COMMENT_LIKES_TABLE = '{{%comment_likes}}';
    private const MOVIES_TABLE = '{{%movies}}';
    private const REVIEWS_TABLE = '{{%reviews}}';
    private const USERS_TABLE = '{{%users}}';

    public function findComments(
        int $limit,
        int $offset,
        ?int $movieId = null,
        ?int $reviewId = null,
        ?int $userId = null,
        ?int $parentId = null
    ): array {
        return $this->publicCommentQuery($movieId, $reviewId, $userId, $parentId)
            ->orderBy(['c.created_at' => SORT_ASC, 'c.id' => SORT_ASC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countComments(
        ?int $movieId = null,
        ?int $reviewId = null,
        ?int $userId = null,
        ?int $parentId = null
    ): int {
        return (int) $this->publicCommentQuery($movieId, $reviewId, $userId, $parentId)
            ->count('*', $this->db());
    }

    public function findComment(int $commentId): ?array
    {
        $comment = $this->publicCommentQuery(filterByParent: false)
            ->andWhere(['c.id' => $commentId])
            ->one($this->db());

        return $comment === false ? null : $comment;
    }

    public function findOwnedComment(int $commentId, int $userId): ?array
    {
        $comment = $this->commentQuery()
            ->andWhere([
                'c.id' => $commentId,
                'c.user_id' => $userId,
                'c.deleted_at' => null,
            ])
            ->one($this->db());

        return $comment === false ? null : $comment;
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

    public function reviewExists(int $reviewId): bool
    {
        return (new Query())
            ->from(['r' => self::REVIEWS_TABLE])
            ->innerJoin(['m' => self::MOVIES_TABLE], 'm.id = r.movie_id')
            ->innerJoin(['u' => self::USERS_TABLE], 'u.id = r.user_id')
            ->andWhere([
                'r.id' => $reviewId,
                'r.status' => ReviewStatus::Published->value,
                'r.deleted_at' => null,
                'm.status' => MovieStatus::Active->value,
                'm.deleted_at' => null,
                'u.status' => 'active',
                'u.deleted_at' => null,
            ])
            ->exists($this->db());
    }

    public function createComment(int $userId, array $attributes): array
    {
        $now = new Expression('NOW()');
        $this->db()->createCommand()->insert(self::COMMENTS_TABLE, [
            'user_id' => $userId,
            'movie_id' => $attributes['movie_id'] ?? null,
            'review_id' => $attributes['review_id'] ?? null,
            'parent_id' => $attributes['parent_id'] ?? null,
            'body' => $attributes['body'],
            'likes_count' => 0,
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $userId,
            'updated_by' => $userId,
        ])->execute();

        $commentId = (int) $this->db()->getLastInsertID();
        $comment = $this->findOwnedComment($commentId, $userId);

        if ($comment === null) {
            throw new CommentException(
                'Comment was created but could not be loaded',
                500,
                CommentException::CODE_COMMENT_CREATE_FAILED
            );
        }

        return $comment;
    }

    public function updateComment(int $commentId, int $userId, array $attributes): void
    {
        $attributes['updated_at'] = new Expression('NOW()');
        $attributes['updated_by'] = $userId;

        $this->db()->createCommand()
            ->update(self::COMMENTS_TABLE, $attributes, [
                'id' => $commentId,
                'user_id' => $userId,
                'deleted_at' => null,
            ])
            ->execute();
    }

    public function softDeleteComment(int $commentId, int $userId): void
    {
        $now = new Expression('NOW()');
        $this->db()->createCommand()
            ->update(self::COMMENTS_TABLE, [
                'deleted_at' => $now,
                'deleted_by' => $userId,
                'updated_at' => $now,
                'updated_by' => $userId,
            ], [
                'id' => $commentId,
                'user_id' => $userId,
                'deleted_at' => null,
            ])
            ->execute();
    }

    public function likeComment(int $commentId, int $userId): array
    {
        try {
            $this->db()->createCommand()->insert(self::COMMENT_LIKES_TABLE, [
                'comment_id' => $commentId,
                'user_id' => $userId,
                'created_at' => new Expression('NOW()'),
            ])->execute();
        } catch (IntegrityException) {
        }

        $likesCount = (int) (new Query())
            ->from(self::COMMENT_LIKES_TABLE)
            ->andWhere(['comment_id' => $commentId])
            ->count('*', $this->db());

        $this->db()->createCommand()
            ->update(self::COMMENTS_TABLE, ['likes_count' => $likesCount], ['id' => $commentId])
            ->execute();

        $comment = $this->findComment($commentId);

        if ($comment === null) {
            throw new CommentException('Comment not found', 404, CommentException::CODE_COMMENT_NOT_FOUND);
        }

        return $comment;
    }

    private function publicCommentQuery(
        ?int $movieId = null,
        ?int $reviewId = null,
        ?int $userId = null,
        ?int $parentId = null,
        bool $filterByParent = true
    ): Query {
        $commentQuery = $this->commentQuery()
            ->andWhere(['c.deleted_at' => null])
            ->andWhere(['u.status' => 'active'])
            ->andWhere(['u.deleted_at' => null])
            ->andFilterWhere(['c.movie_id' => $movieId])
            ->andFilterWhere(['c.review_id' => $reviewId])
            ->andFilterWhere(['c.user_id' => $userId])
            ->andWhere([
                'or',
                [
                    'and',
                    ['not', ['c.movie_id' => null]],
                    ['m.status' => MovieStatus::Active->value],
                    ['m.deleted_at' => null],
                ],
                [
                    'and',
                    ['not', ['c.review_id' => null]],
                    ['r.status' => ReviewStatus::Published->value],
                    ['r.deleted_at' => null],
                    ['rm.status' => MovieStatus::Active->value],
                    ['rm.deleted_at' => null],
                    ['ru.status' => 'active'],
                    ['ru.deleted_at' => null],
                ],
            ]);

        if ($filterByParent) {
            $parentId === null
                ? $commentQuery->andWhere(['c.parent_id' => null])
                : $commentQuery->andWhere(['c.parent_id' => $parentId]);
        }

        return $commentQuery;
    }

    private function commentQuery(): Query
    {
        return (new Query())
            ->select([
                'id' => 'c.id',
                'user_id' => 'c.user_id',
                'movie_id' => 'c.movie_id',
                'review_id' => 'c.review_id',
                'parent_id' => 'c.parent_id',
                'body' => 'c.body',
                'likes_count' => 'c.likes_count',
                'created_at' => 'c.created_at',
                'updated_at' => 'c.updated_at',
                'username' => 'u.username',
                'user_avatar_url' => 'u.avatar_url',
                'movie_slug' => 'm.slug',
                'movie_title' => 'm.title',
                'movie_poster_url' => 'm.poster_url',
                'movie_release_date' => 'm.release_date',
                'review_title' => 'r.title',
                'review_movie_id' => 'r.movie_id',
            ])
            ->from(['c' => self::COMMENTS_TABLE])
            ->leftJoin(['u' => self::USERS_TABLE], 'u.id = c.user_id')
            ->leftJoin(['m' => self::MOVIES_TABLE], 'm.id = c.movie_id')
            ->leftJoin(['r' => self::REVIEWS_TABLE], 'r.id = c.review_id')
            ->leftJoin(['rm' => self::MOVIES_TABLE], 'rm.id = r.movie_id')
            ->leftJoin(['ru' => self::USERS_TABLE], 'ru.id = r.user_id');
    }
}
