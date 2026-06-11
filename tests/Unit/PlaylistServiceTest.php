<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Playlist\Exceptions\PlaylistException;
use App\Modules\Playlist\Interfaces\PlaylistRepositoryInterface;
use App\Modules\Playlist\Mappers\PlaylistMapper;
use App\Modules\Playlist\Requests\PlaylistMovieRequest;
use App\Modules\Playlist\Requests\PlaylistRequest;
use App\Modules\Playlist\Services\PlaylistService;
use App\Modules\Playlist\Transformers\PlaylistTransformer;
use PHPUnit\Framework\TestCase;

final class PlaylistServiceTest extends TestCase
{
    public function testIndexReturnsCurrentUserPlaylists(): void
    {
        $repository = $this->createMock(PlaylistRepositoryInterface::class);
        $repository
            ->method('findForUser')
            ->with(7, 20, 0, null, null)
            ->willReturn([$this->playlistRow()]);
        $repository
            ->method('countForUser')
            ->with(7, null, null)
            ->willReturn(1);
        $repository
            ->method('getPlaylistStats')
            ->with(15)
            ->willReturn(['movies_count' => 2]);

        $payload = $this->service($repository)->index(new PlaylistRequest(), 7)->toArray();

        self::assertSame('Weekend watchlist', $payload['items'][0]['name']);
        self::assertSame(2, $payload['items'][0]['stats']['movies_count']);
        self::assertSame(1, $payload['pagination']['total']);
    }

    public function testViewReturnsPlaylistWithMovies(): void
    {
        $repository = $this->createMock(PlaylistRepositoryInterface::class);
        $repository
            ->method('findAccessiblePlaylist')
            ->with(15, 7)
            ->willReturn($this->playlistRow());
        $repository
            ->method('findPlaylistMovies')
            ->with(15)
            ->willReturn([$this->playlistMovieRow()]);
        $repository
            ->method('getPlaylistStats')
            ->with(15)
            ->willReturn(['movies_count' => 1]);

        $payload = $this->service($repository)->view(15, 7)->toArray();

        self::assertSame('Weekend watchlist', $payload['playlist']['name']);
        self::assertSame('Blade Runner 2049', $payload['playlist']['movies'][0]['movie']['title']);
    }

    public function testCreateGeneratesUniqueSlugAndPersistsPlaylist(): void
    {
        $repository = $this->createMock(PlaylistRepositoryInterface::class);
        $repository
            ->method('slugExistsForUser')
            ->willReturnOnConsecutiveCalls(true, false);
        $repository
            ->expects($this->once())
            ->method('createPlaylist')
            ->with(7, self::callback(
                static fn (array $attributes): bool => $attributes['slug'] === 'watch-later-2'
                    && $attributes['visibility'] === 'private'
            ))
            ->willReturn(array_merge($this->playlistRow(), [
                'slug' => 'watch-later-2',
                'visibility' => 'private',
            ]));
        $repository
            ->method('getPlaylistStats')
            ->willReturn(['movies_count' => 0]);

        $request = new PlaylistRequest(['scenario' => PlaylistRequest::SCENARIO_CREATE]);
        $request->loadFromArray(['name' => 'Watch Later']);

        $payload = $this->service($repository)->create($request, 7)->toArray();

        self::assertSame('watch-later-2', $payload['playlist']['slug']);
        self::assertSame('private', $payload['playlist']['visibility']);
    }

    public function testViewThrowsWhenPlaylistIsMissingOrPrivate(): void
    {
        $repository = $this->createMock(PlaylistRepositoryInterface::class);
        $repository
            ->method('findAccessiblePlaylist')
            ->with(404, 7)
            ->willReturn(null);

        $this->expectException(PlaylistException::class);
        $this->expectExceptionMessage('Playlist not found');

        $this->service($repository)->view(404, 7);
    }

    public function testAddMovieAddsActiveMovieToOwnedPlaylist(): void
    {
        $repository = $this->createMock(PlaylistRepositoryInterface::class);
        $repository
            ->method('findOwnedPlaylist')
            ->with(15, 7)
            ->willReturn($this->playlistRow());
        $repository
            ->method('movieExists')
            ->with(10)
            ->willReturn(true);
        $repository
            ->method('nextPosition')
            ->with(15)
            ->willReturn(2);
        $repository
            ->expects($this->once())
            ->method('addMovieToPlaylist')
            ->with(15, 10, 2)
            ->willReturn($this->playlistMovieRow());

        $request = new PlaylistMovieRequest();
        $request->loadFromArray(['movie_id' => 10]);

        $payload = $this->service($repository)->addMovie(15, $request, 7)->toArray();

        self::assertSame('Movie added to playlist', $payload['message']);
        self::assertSame('Blade Runner 2049', $payload['item']['movie']['title']);
    }

    public function testRemoveMovieThrowsWhenMovieIsNotInPlaylist(): void
    {
        $repository = $this->createMock(PlaylistRepositoryInterface::class);
        $repository
            ->method('findOwnedPlaylist')
            ->with(15, 7)
            ->willReturn($this->playlistRow());
        $repository
            ->method('removeMovieFromPlaylist')
            ->with(15, 10)
            ->willReturn(false);

        $this->expectException(PlaylistException::class);
        $this->expectExceptionMessage('Movie was not found in playlist');

        $this->service($repository)->removeMovie(15, 10, 7);
    }

    private function service(PlaylistRepositoryInterface $repository): PlaylistService
    {
        return new PlaylistService($repository, new PlaylistMapper(), new PlaylistTransformer());
    }

    /**
     * @return array<string, mixed>
     */
    private function playlistRow(): array
    {
        return [
            'id' => 15,
            'user_id' => 7,
            'name' => 'Weekend watchlist',
            'slug' => 'weekend-watchlist',
            'description' => 'Films for Saturday.',
            'visibility' => 'private',
            'is_default_watch_later' => false,
            'created_at' => '2026-05-18 10:00:00',
            'updated_at' => '2026-05-18 10:00:00',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function playlistMovieRow(): array
    {
        return [
            'playlist_movie_id' => 31,
            'position' => 2,
            'added_at' => '2026-05-18 11:00:00',
            'movie_id' => 10,
            'movie_slug' => 'blade-runner-2049',
            'movie_title' => 'Blade Runner 2049',
            'movie_poster_url' => 'https://example.com/poster.jpg',
            'movie_release_date' => '2017-10-06',
        ];
    }
}
