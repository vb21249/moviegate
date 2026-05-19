<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Services;

use App\Common\Services\AbstractService;
use App\Modules\Playlist\Enums\PlaylistVisibility;
use App\Modules\Playlist\Exceptions\PlaylistException;
use App\Modules\Playlist\Interfaces\PlaylistRepositoryInterface;
use App\Modules\Playlist\Interfaces\PlaylistServiceInterface;
use App\Modules\Playlist\Mappers\PlaylistMapper;
use App\Modules\Playlist\Requests\PlaylistMovieRequest;
use App\Modules\Playlist\Requests\PlaylistRequest;
use App\Modules\Playlist\Responses\PlaylistResponse;
use App\Modules\Playlist\Transformers\PlaylistTransformer;

/**
 * Playlist application service for user playlist management.
 */
final class PlaylistService extends AbstractService implements PlaylistServiceInterface
{
    public function __construct(
        private readonly PlaylistRepositoryInterface $repository,
        private readonly PlaylistMapper $mapper,
        private readonly PlaylistTransformer $transformer,
    ) {
    }

    public function index(PlaylistRequest $request, int $userId): PlaylistResponse
    {
        $playlists = array_map(
            fn (array $playlist): array => $this->mapPlaylist($playlist)->toArray(),
            $this->repository->findForUser(
                $userId,
                $request->limit(),
                $request->offset(),
                $request->requestedVisibility(),
                $request->query()
            )
        );

        return new PlaylistResponse([
            'items' => $playlists,
            'pagination' => $this->pagination(
                $request,
                $this->repository->countForUser($userId, $request->requestedVisibility(), $request->query())
            ),
        ]);
    }

    public function view(int $playlistId, int $userId): PlaylistResponse
    {
        $playlist = $this->findAccessiblePlaylistOrFail($playlistId, $userId);
        $movies = $this->playlistMovieItems($playlistId);

        return new PlaylistResponse([
            'playlist' => $this->mapPlaylist($playlist, $movies)->toArray(),
        ]);
    }

    public function create(PlaylistRequest $request, int $userId): PlaylistResponse
    {
        $attributes = $request->playlistAttributes();
        $attributes['visibility'] = $attributes['visibility'] ?? PlaylistVisibility::Private->value;
        $attributes['slug'] = $this->uniqueSlug(
            $userId,
            (string) ($attributes['slug'] ?? $attributes['name'])
        );

        $playlist = $this->repository->createPlaylist($userId, $attributes);

        if ((bool) ($attributes['is_default_watch_later'] ?? false)) {
            $this->repository->clearDefaultWatchLater($userId, (int) $playlist['id']);
        }

        return new PlaylistResponse([
            'playlist' => $this->mapPlaylist($playlist)->toArray(),
        ]);
    }

    public function update(int $playlistId, PlaylistRequest $request, int $userId): PlaylistResponse
    {
        $playlist = $this->findOwnedPlaylistOrFail($playlistId, $userId);
        $attributes = $request->playlistAttributes();

        if ($attributes === []) {
            throw new PlaylistException(
                'At least one playlist field must be provided',
                422,
                PlaylistException::CODE_VALIDATION_ERROR
            );
        }

        if (array_key_exists('slug', $attributes)) {
            $attributes['slug'] = $this->uniqueSlug($userId, (string) $attributes['slug'], $playlistId);
        }

        $this->repository->updatePlaylist($playlistId, $attributes);

        if ((bool) ($attributes['is_default_watch_later'] ?? false)) {
            $this->repository->clearDefaultWatchLater($userId, $playlistId);
        }

        $updatedPlaylist = $this->repository->findOwnedPlaylist($playlistId, $userId) ?? $playlist;

        return new PlaylistResponse([
            'playlist' => $this->mapPlaylist($updatedPlaylist)->toArray(),
        ]);
    }

    public function delete(int $playlistId, int $userId): PlaylistResponse
    {
        $this->findOwnedPlaylistOrFail($playlistId, $userId);
        $this->repository->softDeletePlaylist($playlistId, $userId);

        return new PlaylistResponse([
            'deleted' => true,
            'playlist_id' => $playlistId,
        ]);
    }

    public function addMovie(int $playlistId, PlaylistMovieRequest $request, int $userId): PlaylistResponse
    {
        $this->findOwnedPlaylistOrFail($playlistId, $userId);

        if (!$this->repository->movieExists($request->movieId())) {
            throw new PlaylistException('Movie not found', 404, PlaylistException::CODE_MOVIE_NOT_FOUND);
        }

        $position = $request->position() ?? $this->repository->nextPosition($playlistId);
        $row = $this->repository->addMovieToPlaylist($playlistId, $request->movieId(), $position);
        $item = $this->transformer->movieItem($row);

        return new PlaylistResponse($this->transformer->movieAddedPayload($playlistId, $item));
    }

    public function removeMovie(int $playlistId, int $movieId, int $userId): PlaylistResponse
    {
        $this->findOwnedPlaylistOrFail($playlistId, $userId);

        if (!$this->repository->removeMovieFromPlaylist($playlistId, $movieId)) {
            throw new PlaylistException(
                'Movie was not found in playlist',
                404,
                PlaylistException::CODE_PLAYLIST_MOVIE_NOT_FOUND
            );
        }

        return new PlaylistResponse($this->transformer->movieRemovedPayload($playlistId, $movieId));
    }

    /**
     * @param int $playlistId
     * @param int $userId
     *
     * @return array<string, mixed>
     */
    private function findAccessiblePlaylistOrFail(int $playlistId, int $userId): array
    {
        $playlist = $this->repository->findAccessiblePlaylist($playlistId, $userId);

        if ($playlist === null) {
            throw new PlaylistException('Playlist not found', 404, PlaylistException::CODE_PLAYLIST_NOT_FOUND);
        }

        return $playlist;
    }

    /**
     * @param int $playlistId
     * @param int $userId
     *
     * @return array<string, mixed>
     */
    private function findOwnedPlaylistOrFail(int $playlistId, int $userId): array
    {
        $playlist = $this->repository->findOwnedPlaylist($playlistId, $userId);

        if ($playlist === null) {
            throw new PlaylistException('Playlist not found', 404, PlaylistException::CODE_PLAYLIST_NOT_FOUND);
        }

        return $playlist;
    }

    /**
     * @param array<string, mixed> $playlist
     * @param list<array<string, mixed>> $movies
     *
     * @return \App\Modules\Playlist\DTO\PlaylistDto
     */
    private function mapPlaylist(array $playlist, array $movies = []): \App\Modules\Playlist\DTO\PlaylistDto
    {
        return $this->mapper->mapPlaylist(
            $playlist,
            $this->repository->getPlaylistStats((int) $playlist['id']),
            $movies
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function playlistMovieItems(int $playlistId): array
    {
        return array_map(
            fn (array $row): array => $this->transformer->movieItem($row),
            $this->repository->findPlaylistMovies($playlistId)
        );
    }

    /**
     * @param PlaylistRequest $request
     * @param int $total
     *
     * @return array<string, int|bool>
     */
    private function pagination(PlaylistRequest $request, int $total): array
    {
        $limit = $request->limit();
        $offset = $request->offset();

        return [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
            'has_more' => $offset + $limit < $total,
        ];
    }

    private function uniqueSlug(int $userId, string $source, ?int $excludePlaylistId = null): string
    {
        $baseSlug = $this->slugify($source);
        $slug = $baseSlug;
        $suffix = 2;

        while ($this->repository->slugExistsForUser($userId, $slug, $excludePlaylistId)) {
            $slug = $baseSlug . '-' . $suffix;
            ++$suffix;
        }

        return $slug;
    }

    private function slugify(string $source): string
    {
        $slug = mb_strtolower(trim($source));
        $slug = (string) preg_replace('/[^a-z0-9]+/u', '-', $slug);
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'playlist';
    }
}
