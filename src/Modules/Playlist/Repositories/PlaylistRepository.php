<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Movie\Enums\MovieStatus;
use App\Modules\Playlist\Enums\PlaylistVisibility;
use App\Modules\Playlist\Exceptions\PlaylistException;
use App\Modules\Playlist\Interfaces\PlaylistRepositoryInterface;
use yii\db\Expression;
use yii\db\Query;

/**
 * Playlist repository backed by playlists and playlist_movies tables.
 */
final class PlaylistRepository extends BaseRepository implements PlaylistRepositoryInterface
{
    private const PLAYLISTS_TABLE = '{{%playlists}}';
    private const PLAYLIST_MOVIES_TABLE = '{{%playlist_movies}}';
    private const MOVIES_TABLE = '{{%movies}}';

    public function findForUser(
        int $userId,
        int $limit,
        int $offset,
        ?string $visibility = null,
        ?string $query = null
    ): array {
        return $this->basePlaylistQuery()
            ->andWhere(['p.user_id' => $userId])
            ->andFilterWhere(['p.visibility' => $visibility])
            ->andFilterWhere($this->playlistSearchCondition($query))
            ->orderBy(['p.updated_at' => SORT_DESC, 'p.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countForUser(int $userId, ?string $visibility = null, ?string $query = null): int
    {
        return (int) $this->basePlaylistQuery()
            ->andWhere(['p.user_id' => $userId])
            ->andFilterWhere(['p.visibility' => $visibility])
            ->andFilterWhere($this->playlistSearchCondition($query))
            ->count('*', $this->db());
    }

    public function findAccessiblePlaylist(int $playlistId, int $userId): ?array
    {
        $playlist = $this->basePlaylistQuery()
            ->andWhere(['p.id' => $playlistId])
            ->andWhere([
                'or',
                ['p.user_id' => $userId],
                ['p.visibility' => [PlaylistVisibility::Public->value, PlaylistVisibility::Unlisted->value]],
            ])
            ->one($this->db());

        return $playlist === false ? null : $playlist;
    }

    public function findOwnedPlaylist(int $playlistId, int $userId): ?array
    {
        $playlist = $this->basePlaylistQuery()
            ->andWhere(['p.id' => $playlistId, 'p.user_id' => $userId])
            ->one($this->db());

        return $playlist === false ? null : $playlist;
    }

    public function getPlaylistStats(int $playlistId): array
    {
        return [
            'movies_count' => $this->countRows(self::PLAYLIST_MOVIES_TABLE, [
                'playlist_id' => $playlistId,
                'deleted_at' => null,
            ]),
        ];
    }

    public function findPlaylistMovies(int $playlistId): array
    {
        return (new Query())
            ->select($this->playlistMovieSelect())
            ->from(['pm' => self::PLAYLIST_MOVIES_TABLE])
            ->innerJoin(['m' => self::MOVIES_TABLE], 'm.id = pm.movie_id')
            ->andWhere(['pm.playlist_id' => $playlistId])
            ->andWhere(['pm.deleted_at' => null])
            ->andWhere(['m.status' => MovieStatus::Active->value])
            ->andWhere(['m.deleted_at' => null])
            ->orderBy(['pm.position' => SORT_ASC, 'pm.id' => SORT_ASC])
            ->all($this->db());
    }

    public function createPlaylist(int $userId, array $attributes): array
    {
        $now = new Expression('NOW()');
        $this->db()->createCommand()->insert(self::PLAYLISTS_TABLE, [
            'user_id' => $userId,
            'name' => $attributes['name'],
            'slug' => $attributes['slug'],
            'description' => $attributes['description'] ?? null,
            'visibility' => $attributes['visibility'],
            'is_default_watch_later' => (bool) ($attributes['is_default_watch_later'] ?? false),
            'created_at' => $now,
            'updated_at' => $now,
            'created_by' => $userId,
            'updated_by' => $userId,
        ])->execute();

        $playlistId = (int) $this->db()->getLastInsertID();
        $playlist = $this->findOwnedPlaylist($playlistId, $userId);

        if ($playlist === null) {
            throw new PlaylistException(
                'Playlist was created but could not be loaded',
                500,
                PlaylistException::CODE_PLAYLIST_CREATE_FAILED
            );
        }

        return $playlist;
    }

    public function updatePlaylist(int $playlistId, array $attributes): void
    {
        $attributes['updated_at'] = new Expression('NOW()');

        $this->db()->createCommand()
            ->update(self::PLAYLISTS_TABLE, $attributes, ['id' => $playlistId])
            ->execute();
    }

    public function softDeletePlaylist(int $playlistId, int $userId): void
    {
        $now = new Expression('NOW()');
        $transaction = $this->db()->beginTransaction();

        try {
            $this->db()->createCommand()
                ->update(self::PLAYLISTS_TABLE, [
                    'deleted_at' => $now,
                    'deleted_by' => $userId,
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ], ['id' => $playlistId])
                ->execute();

            $this->db()->createCommand()
                ->update(self::PLAYLIST_MOVIES_TABLE, [
                    'deleted_at' => $now,
                    'deleted_by' => $userId,
                    'updated_at' => $now,
                    'updated_by' => $userId,
                ], ['playlist_id' => $playlistId, 'deleted_at' => null])
                ->execute();

            $transaction->commit();
        } catch (\Throwable $throwable) {
            $transaction->rollBack();

            throw $throwable;
        }
    }

    public function slugExistsForUser(int $userId, string $slug, ?int $excludePlaylistId = null): bool
    {
        $query = (new Query())
            ->from(self::PLAYLISTS_TABLE)
            ->andWhere([
                'user_id' => $userId,
                'slug' => $slug,
                'deleted_at' => null,
            ]);

        if ($excludePlaylistId !== null) {
            $query->andWhere(['not', ['id' => $excludePlaylistId]]);
        }

        return $query->exists($this->db());
    }

    public function clearDefaultWatchLater(int $userId, ?int $excludePlaylistId = null): void
    {
        $condition = [
            'user_id' => $userId,
            'is_default_watch_later' => true,
            'deleted_at' => null,
        ];

        if ($excludePlaylistId !== null) {
            $condition = ['and', $condition, ['not', ['id' => $excludePlaylistId]]];
        }

        $this->db()->createCommand()
            ->update(self::PLAYLISTS_TABLE, [
                'is_default_watch_later' => false,
                'updated_at' => new Expression('NOW()'),
            ], $condition)
            ->execute();
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

    public function nextPosition(int $playlistId): int
    {
        $position = (new Query())
            ->from(self::PLAYLIST_MOVIES_TABLE)
            ->andWhere(['playlist_id' => $playlistId])
            ->andWhere(['deleted_at' => null])
            ->max('position', $this->db());

        return $position === null ? 0 : (int) $position + 1;
    }

    public function addMovieToPlaylist(int $playlistId, int $movieId, int $position): array
    {
        $now = new Expression('NOW()');
        $this->db()->createCommand()->upsert(
            self::PLAYLIST_MOVIES_TABLE,
            [
                'playlist_id' => $playlistId,
                'movie_id' => $movieId,
                'position' => $position,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'position' => $position,
                'updated_at' => $now,
                'deleted_at' => null,
            ]
        )->execute();

        $row = $this->findPlaylistMovie($playlistId, $movieId);

        if ($row === null) {
            throw new PlaylistException(
                'Movie was added but could not be loaded',
                500,
                PlaylistException::CODE_PLAYLIST_CREATE_FAILED
            );
        }

        return $row;
    }

    public function removeMovieFromPlaylist(int $playlistId, int $movieId): bool
    {
        $affectedRows = $this->db()->createCommand()
            ->update(self::PLAYLIST_MOVIES_TABLE, [
                'deleted_at' => new Expression('NOW()'),
                'updated_at' => new Expression('NOW()'),
            ], [
                'playlist_id' => $playlistId,
                'movie_id' => $movieId,
                'deleted_at' => null,
            ])
            ->execute();

        return $affectedRows > 0;
    }

    /**
     * @return Query
     */
    private function basePlaylistQuery(): Query
    {
        return (new Query())
            ->select([
                'id' => 'p.id',
                'user_id' => 'p.user_id',
                'name' => 'p.name',
                'slug' => 'p.slug',
                'description' => 'p.description',
                'visibility' => 'p.visibility',
                'is_default_watch_later' => 'p.is_default_watch_later',
                'created_at' => 'p.created_at',
                'updated_at' => 'p.updated_at',
            ])
            ->from(['p' => self::PLAYLISTS_TABLE])
            ->andWhere(['p.deleted_at' => null]);
    }

    /**
     * @param string|null $query
     *
     * @return array<int|string, mixed>
     */
    private function playlistSearchCondition(?string $query): array
    {
        $query = $query !== null ? trim($query) : '';

        if ($query === '') {
            return [];
        }

        return [
            'or',
            ['like', 'p.name', $query],
            ['like', 'p.slug', $query],
            ['like', 'p.description', $query],
        ];
    }

    /**
     * @param int $playlistId
     * @param int $movieId
     *
     * @return array<string, mixed>|null
     */
    private function findPlaylistMovie(int $playlistId, int $movieId): ?array
    {
        $row = (new Query())
            ->select($this->playlistMovieSelect())
            ->from(['pm' => self::PLAYLIST_MOVIES_TABLE])
            ->innerJoin(['m' => self::MOVIES_TABLE], 'm.id = pm.movie_id')
            ->andWhere([
                'pm.playlist_id' => $playlistId,
                'pm.movie_id' => $movieId,
                'pm.deleted_at' => null,
                'm.status' => MovieStatus::Active->value,
                'm.deleted_at' => null,
            ])
            ->one($this->db());

        return $row === false ? null : $row;
    }

    /**
     * @return array<string, string>
     */
    private function playlistMovieSelect(): array
    {
        return [
            'playlist_movie_id' => 'pm.id',
            'position' => 'pm.position',
            'added_at' => 'pm.created_at',
            'movie_id' => 'm.id',
            'movie_slug' => 'm.slug',
            'movie_title' => 'm.title',
            'movie_poster_url' => 'm.poster_url',
            'movie_release_date' => 'm.release_date',
        ];
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
