<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Contracts\CorrelationIdProviderInterface;
use App\Modules\Integration\Clients\OmdbClientInterface;
use App\Modules\Integration\Clients\TmdbClientInterface;
use App\Modules\Integration\Exceptions\IntegrationException;
use App\Modules\Integration\Interfaces\IntegrationRepositoryInterface;
use App\Modules\Integration\Mappers\OmdbMovieMapper;
use App\Modules\Integration\Mappers\TmdbMovieMapper;
use App\Modules\Integration\Requests\IntegrationRequest;
use App\Modules\Integration\Services\IntegrationService;
use App\Modules\Movie\Interfaces\MovieRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class IntegrationServiceTest extends TestCase
{
    public function testSyncTmdbByIdImportsMovieAndWritesAuditLog(): void
    {
        $repository = $this->createMock(IntegrationRepositoryInterface::class);
        $movieRepository = $this->createMock(MovieRepositoryInterface::class);
        $tmdbClient = $this->createMock(TmdbClientInterface::class);

        $tmdbClient
            ->expects($this->once())
            ->method('movieDetails')
            ->with(335984, 'en-US')
            ->willReturn($this->tmdbMovie());
        $tmdbClient
            ->expects($this->never())
            ->method('searchMovies');

        $movieRepository
            ->expects($this->once())
            ->method('upsertImportedMovie')
            ->with(self::callback(static function (array $movie): bool {
                return $movie['tmdb_id'] === 335984
                    && $movie['slug'] === 'blade-runner-2049-2017-335984'
                    && $movie['runtime_minutes'] === 164
                    && $movie['language'] === 'en-US'
                    && $movie['status'] === 'active';
            }))
            ->willReturn($this->movieRow());
        $repository
            ->expects($this->once())
            ->method('logIntegration')
            ->with(
                'test-correlation-id',
                'tmdb',
                'movie_sync',
                self::callback(static fn (array $payload): bool => $payload['tmdb_id'] === 335984),
                self::callback(static fn (array $payload): bool => $payload['synced'] === true),
                'success'
            )
            ->willReturn(7);

        $response = $this->service($repository, $movieRepository, $tmdbClient)
            ->syncTmdb($this->request(['tmdb_id' => 335984]))
            ->toArray();

        self::assertTrue($response['synced']);
        self::assertSame('tmdb', $response['provider']);
        self::assertSame(335984, $response['movie']['tmdb_id']);
        self::assertSame(7, $response['log_id']);
    }

    public function testSyncTmdbByQuerySearchesFirstAndImportsDetails(): void
    {
        $repository = $this->createMock(IntegrationRepositoryInterface::class);
        $movieRepository = $this->createMock(MovieRepositoryInterface::class);
        $tmdbClient = $this->createMock(TmdbClientInterface::class);

        $tmdbClient
            ->expects($this->once())
            ->method('searchMovies')
            ->with('Blade Runner 2049', 'uk-UA', false)
            ->willReturn(['results' => [['id' => 335984]]]);
        $tmdbClient
            ->expects($this->once())
            ->method('movieDetails')
            ->with(335984, 'uk-UA')
            ->willReturn($this->tmdbMovie());
        $movieRepository
            ->expects($this->once())
            ->method('upsertImportedMovie')
            ->willReturn($this->movieRow());
        $repository
            ->expects($this->once())
            ->method('logIntegration')
            ->willReturn(8);

        $response = $this->service($repository, $movieRepository, $tmdbClient)
            ->syncTmdb($this->request(['query' => 'Blade Runner 2049', 'language' => 'uk-UA']))
            ->toArray();

        self::assertSame('query', $response['source']['matched_by']);
        self::assertSame('uk-UA', $response['source']['language']);
    }

    public function testSyncTmdbByQueryThrowsWhenNothingFound(): void
    {
        $repository = $this->createMock(IntegrationRepositoryInterface::class);
        $movieRepository = $this->createMock(MovieRepositoryInterface::class);
        $tmdbClient = $this->createMock(TmdbClientInterface::class);

        $tmdbClient
            ->expects($this->once())
            ->method('searchMovies')
            ->willReturn(['results' => []]);
        $repository
            ->expects($this->once())
            ->method('logIntegration')
            ->with(
                'test-correlation-id',
                'tmdb',
                'movie_sync',
                self::anything(),
                self::callback(static fn (array $payload): bool => $payload['error_code'] === IntegrationException::CODE_TMDB_NOT_FOUND),
                'failed'
            )
            ->willReturn(9);

        try {
            $this->service($repository, $movieRepository, $tmdbClient)
                ->syncTmdb($this->request(['query' => 'missing movie']));
            self::fail('Expected IntegrationException.');
        } catch (IntegrationException $exception) {
            self::assertSame(IntegrationException::CODE_TMDB_NOT_FOUND, $exception->getErrorCode());
        }
    }

    public function testSyncOmdbByImdbIdImportsMovieAndWritesAuditLog(): void
    {
        $repository = $this->createMock(IntegrationRepositoryInterface::class);
        $movieRepository = $this->createMock(MovieRepositoryInterface::class);
        $tmdbClient = $this->createMock(TmdbClientInterface::class);
        $omdbClient = $this->createMock(OmdbClientInterface::class);

        $omdbClient
            ->expects($this->once())
            ->method('movieDetails')
            ->with('tt1856101')
            ->willReturn($this->omdbMovie());
        $omdbClient
            ->expects($this->never())
            ->method('searchMovies');

        $movieRepository
            ->expects($this->once())
            ->method('upsertImportedMovie')
            ->with(self::callback(static function (array $movie): bool {
                return $movie['source_provider'] === 'omdb'
                    && $movie['external_id'] === 'tt1856101'
                    && $movie['imdb_id'] === 'tt1856101'
                    && $movie['slug'] === 'blade-runner-2049-2017-tt1856101'
                    && $movie['runtime_minutes'] === 164
                    && $movie['status'] === 'active';
            }))
            ->willReturn($this->movieRow());
        $repository
            ->expects($this->once())
            ->method('logIntegration')
            ->with(
                'test-correlation-id',
                'omdb',
                'movie_sync',
                self::callback(static fn (array $payload): bool => $payload['imdb_id'] === 'tt1856101'),
                self::callback(static fn (array $payload): bool => $payload['synced'] === true),
                'success'
            )
            ->willReturn(11);

        $response = $this->service($repository, $movieRepository, $tmdbClient, $omdbClient)
            ->syncOmdb($this->request(['imdb_id' => 'tt1856101']))
            ->toArray();

        self::assertTrue($response['synced']);
        self::assertSame('omdb', $response['provider']);
        self::assertSame('tt1856101', $response['source']['imdb_id']);
        self::assertSame(11, $response['log_id']);
    }

    public function testSyncOmdbByQuerySearchesFirstAndImportsDetails(): void
    {
        $repository = $this->createMock(IntegrationRepositoryInterface::class);
        $movieRepository = $this->createMock(MovieRepositoryInterface::class);
        $tmdbClient = $this->createMock(TmdbClientInterface::class);
        $omdbClient = $this->createMock(OmdbClientInterface::class);

        $omdbClient
            ->expects($this->once())
            ->method('searchMovies')
            ->with('Blade Runner 2049')
            ->willReturn(['Search' => [['imdbID' => 'tt1856101']]]);
        $omdbClient
            ->expects($this->once())
            ->method('movieDetails')
            ->with('tt1856101')
            ->willReturn($this->omdbMovie());
        $movieRepository
            ->expects($this->once())
            ->method('upsertImportedMovie')
            ->willReturn($this->movieRow());
        $repository
            ->expects($this->once())
            ->method('logIntegration')
            ->willReturn(12);

        $response = $this->service($repository, $movieRepository, $tmdbClient, $omdbClient)
            ->syncOmdb($this->request(['query' => 'Blade Runner 2049']))
            ->toArray();

        self::assertSame('query', $response['source']['matched_by']);
        self::assertSame('tt1856101', $response['source']['imdb_id']);
    }

    public function testSyncOmdbByQueryThrowsWhenNothingFound(): void
    {
        $repository = $this->createMock(IntegrationRepositoryInterface::class);
        $movieRepository = $this->createMock(MovieRepositoryInterface::class);
        $tmdbClient = $this->createMock(TmdbClientInterface::class);
        $omdbClient = $this->createMock(OmdbClientInterface::class);

        $omdbClient
            ->expects($this->once())
            ->method('searchMovies')
            ->willReturn(['Search' => []]);
        $repository
            ->expects($this->once())
            ->method('logIntegration')
            ->with(
                'test-correlation-id',
                'omdb',
                'movie_sync',
                self::anything(),
                self::callback(static fn (array $payload): bool => $payload['error_code'] === IntegrationException::CODE_OMDB_NOT_FOUND),
                'failed'
            )
            ->willReturn(13);

        try {
            $this->service($repository, $movieRepository, $tmdbClient, $omdbClient)
                ->syncOmdb($this->request(['query' => 'missing movie']));
            self::fail('Expected IntegrationException.');
        } catch (IntegrationException $exception) {
            self::assertSame(IntegrationException::CODE_OMDB_NOT_FOUND, $exception->getErrorCode());
        }
    }

    private function service(
        IntegrationRepositoryInterface $repository,
        MovieRepositoryInterface $movieRepository,
        TmdbClientInterface $tmdbClient,
        ?OmdbClientInterface $omdbClient = null
    ): IntegrationService {
        $correlationIdProvider = $this->createMock(CorrelationIdProviderInterface::class);
        $correlationIdProvider
            ->method('get')
            ->willReturn('test-correlation-id');

        return new IntegrationService(
            $repository,
            $movieRepository,
            $tmdbClient,
            $omdbClient ?? $this->createMock(OmdbClientInterface::class),
            new TmdbMovieMapper(),
            new OmdbMovieMapper(),
            $correlationIdProvider
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function request(array $payload): IntegrationRequest
    {
        $request = new IntegrationRequest();

        foreach ($payload as $attribute => $value) {
            $request->{$attribute} = $value;
        }

        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    private function tmdbMovie(): array
    {
        return [
            'id' => 335984,
            'title' => 'Blade Runner 2049',
            'original_title' => 'Blade Runner 2049',
            'overview' => 'A young blade runner discovers a long-buried secret.',
            'poster_path' => null,
            'backdrop_path' => null,
            'release_date' => '2017-10-06',
            'runtime' => 164,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function omdbMovie(): array
    {
        return [
            'imdbID' => 'tt1856101',
            'Title' => 'Blade Runner 2049',
            'Year' => '2017',
            'Released' => '06 Oct 2017',
            'Runtime' => '164 min',
            'Plot' => 'A young blade runner discovers a long-buried secret.',
            'Poster' => 'https://example.com/poster.jpg',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function movieRow(): array
    {
        return [
            'id' => 10,
            'tmdb_id' => 335984,
            'slug' => 'blade-runner-2049-2017-335984',
            'title' => 'Blade Runner 2049',
            'original_title' => 'Blade Runner 2049',
            'overview' => 'A young blade runner discovers a long-buried secret.',
            'poster_url' => null,
            'backdrop_url' => null,
            'release_date' => '2017-10-06',
            'runtime_minutes' => 164,
            'status' => 'active',
            'created_at' => '2026-05-20 10:00:00',
            'updated_at' => '2026-05-20 10:00:00',
        ];
    }

}
