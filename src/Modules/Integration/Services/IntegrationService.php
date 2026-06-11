<?php

declare(strict_types=1);

namespace App\Modules\Integration\Services;

use App\Common\Contracts\CorrelationIdProviderInterface;
use App\Common\Services\AbstractService;
use App\Modules\Integration\Clients\OmdbClientInterface;
use App\Modules\Integration\Clients\TmdbClientInterface;
use App\Modules\Integration\Exceptions\IntegrationException;
use App\Modules\Integration\Interfaces\IntegrationRepositoryInterface;
use App\Modules\Integration\Interfaces\IntegrationServiceInterface;
use App\Modules\Integration\Mappers\OmdbMovieMapper;
use App\Modules\Integration\Mappers\TmdbMovieMapper;
use App\Modules\Integration\Requests\IntegrationRequest;
use App\Modules\Integration\Responses\IntegrationResponse;
use App\Modules\Movie\Interfaces\MovieRepositoryInterface;
use Throwable;
use Yii;

/**
 * Integration application service for external provider orchestration.
 */
final class IntegrationService extends AbstractService implements IntegrationServiceInterface
{
    private const SERVICE_TMDB = 'tmdb';
    private const SERVICE_OMDB = 'omdb';
    private const OPERATION_TMDB_MOVIE_SYNC = 'movie_sync';
    private const OPERATION_OMDB_MOVIE_SYNC = 'movie_sync';

    public function __construct(
        private readonly IntegrationRepositoryInterface $repository,
        private readonly MovieRepositoryInterface $movieRepository,
        private readonly TmdbClientInterface $tmdbClient,
        private readonly OmdbClientInterface $omdbClient,
        private readonly TmdbMovieMapper $tmdbMovieMapper,
        private readonly OmdbMovieMapper $omdbMovieMapper,
        private readonly CorrelationIdProviderInterface $correlationIdProvider,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Integration',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload['query'] ?? $payload),
        ];
    }

    public function syncTmdb(IntegrationRequest $request): IntegrationResponse
    {
        $requestPayload = $request->toPayload();

        try {
            $tmdbMovie = $this->resolveTmdbMovie($request);
            $mappedMovie = $this->tmdbMovieMapper->mapToCatalogMovie(
                $tmdbMovie,
                $this->tmdbImageBaseUri(),
                $request->language()
            );
            $movie = $this->movieRepository->upsertImportedMovie($mappedMovie);
            $responsePayload = [
                'tmdb_id' => $mappedMovie['tmdb_id'],
                'movie_id' => $movie['id'] ?? null,
                'synced' => true,
            ];
            $logId = $this->safeLogIntegration(
                self::SERVICE_TMDB,
                self::OPERATION_TMDB_MOVIE_SYNC,
                $requestPayload,
                $responsePayload,
                'success'
            );

            return new IntegrationResponse([
                'provider' => self::SERVICE_TMDB,
                'operation' => self::OPERATION_TMDB_MOVIE_SYNC,
                'synced' => true,
                'source' => [
                    'tmdb_id' => $mappedMovie['tmdb_id'],
                    'language' => $request->language(),
                    'matched_by' => $request->tmdbId() !== null ? 'tmdb_id' : 'query',
                ],
                'movie' => $movie,
                'log_id' => $logId,
            ]);
        } catch (IntegrationException $exception) {
            $this->safeLogIntegration(self::SERVICE_TMDB, self::OPERATION_TMDB_MOVIE_SYNC, $requestPayload, [
                'error_code' => $exception->getErrorCode(),
                'message' => $exception->getMessage(),
            ], 'failed');

            throw $exception;
        } catch (Throwable $exception) {
            $this->safeLogIntegration(self::SERVICE_TMDB, self::OPERATION_TMDB_MOVIE_SYNC, $requestPayload, [
                'error_code' => IntegrationException::CODE_TMDB_REQUEST_FAILED,
                'message' => $exception->getMessage(),
            ], 'failed');
            Yii::error($exception);

            throw new IntegrationException(
                'TMDB sync failed.',
                502,
                IntegrationException::CODE_TMDB_REQUEST_FAILED
            );
        }
    }

    public function syncOmdb(IntegrationRequest $request): IntegrationResponse
    {
        $requestPayload = $request->toPayload();

        try {
            $omdbMovie = $this->resolveOmdbMovie($request);
            $mappedMovie = $this->omdbMovieMapper->mapToCatalogMovie($omdbMovie);
            $movie = $this->movieRepository->upsertImportedMovie($mappedMovie);
            $responsePayload = [
                'imdb_id' => $mappedMovie['imdb_id'],
                'movie_id' => $movie['id'] ?? null,
                'synced' => true,
            ];
            $logId = $this->safeLogIntegration(
                self::SERVICE_OMDB,
                self::OPERATION_OMDB_MOVIE_SYNC,
                $requestPayload,
                $responsePayload,
                'success'
            );

            return new IntegrationResponse([
                'provider' => self::SERVICE_OMDB,
                'operation' => self::OPERATION_OMDB_MOVIE_SYNC,
                'synced' => true,
                'source' => [
                    'imdb_id' => $mappedMovie['imdb_id'],
                    'matched_by' => $request->imdbId() !== null ? 'imdb_id' : 'query',
                ],
                'movie' => $movie,
                'log_id' => $logId,
            ]);
        } catch (IntegrationException $exception) {
            $this->safeLogIntegration(self::SERVICE_OMDB, self::OPERATION_OMDB_MOVIE_SYNC, $requestPayload, [
                'error_code' => $exception->getErrorCode(),
                'message' => $exception->getMessage(),
            ], 'failed');

            throw $exception;
        } catch (Throwable $exception) {
            $this->safeLogIntegration(self::SERVICE_OMDB, self::OPERATION_OMDB_MOVIE_SYNC, $requestPayload, [
                'error_code' => IntegrationException::CODE_OMDB_REQUEST_FAILED,
                'message' => $exception->getMessage(),
            ], 'failed');
            Yii::error($exception);

            throw new IntegrationException(
                'OMDb sync failed.',
                502,
                IntegrationException::CODE_OMDB_REQUEST_FAILED
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveTmdbMovie(IntegrationRequest $request): array
    {
        if ($request->tmdbId() !== null) {
            return $this->tmdbClient->movieDetails($request->tmdbId(), $request->language());
        }

        $search = $this->tmdbClient->searchMovies(
            (string) $request->query(),
            $request->language(),
            $request->includeAdult()
        );
        $results = isset($search['results']) && is_array($search['results']) ? $search['results'] : [];
        $firstResult = $results[0] ?? null;

        if (!is_array($firstResult) || empty($firstResult['id'])) {
            throw new IntegrationException(
                'TMDB movie not found.',
                404,
                IntegrationException::CODE_TMDB_NOT_FOUND
            );
        }

        return $this->tmdbClient->movieDetails((int) $firstResult['id'], $request->language());
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveOmdbMovie(IntegrationRequest $request): array
    {
        if ($request->imdbId() !== null) {
            return $this->omdbClient->movieDetails($request->imdbId());
        }

        $search = $this->omdbClient->searchMovies((string) $request->query());
        $results = isset($search['Search']) && is_array($search['Search']) ? $search['Search'] : [];
        $firstResult = $results[0] ?? null;

        if (!is_array($firstResult) || empty($firstResult['imdbID'])) {
            throw new IntegrationException(
                'OMDb movie not found.',
                404,
                IntegrationException::CODE_OMDB_NOT_FOUND
            );
        }

        return $this->omdbClient->movieDetails((string) $firstResult['imdbID']);
    }

    private function tmdbImageBaseUri(): string
    {
        if (Yii::$app === null) {
            return 'https://image.tmdb.org/t/p/original/';
        }

        return (string) (Yii::$app->params['tmdb']['imageBaseUri'] ?? 'https://image.tmdb.org/t/p/original/');
    }

    /**
     * @param array<string, mixed> $requestPayload
     * @param array<string, mixed> $responsePayload
     *
     * @return int|null
     */
    private function safeLogIntegration(
        string $service,
        string $operation,
        array $requestPayload,
        array $responsePayload,
        string $status
    ): ?int {
        try {
            return $this->repository->logIntegration(
                $this->correlationIdProvider->get(),
                $service,
                $operation,
                $requestPayload,
                $responsePayload,
                $status
            );
        } catch (Throwable $exception) {
            Yii::warning($exception);

            return null;
        }
    }
}
