<?php

declare(strict_types=1);

namespace App\Modules\Integration\Services;

use App\Common\Contracts\CorrelationIdProviderInterface;
use App\Common\Services\AbstractService;
use App\Modules\Integration\Clients\TmdbClientInterface;
use App\Modules\Integration\Exceptions\IntegrationException;
use App\Modules\Integration\Interfaces\IntegrationRepositoryInterface;
use App\Modules\Integration\Interfaces\IntegrationServiceInterface;
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
    private const OPERATION_TMDB_MOVIE_SYNC = 'movie_sync';

    public function __construct(
        private readonly IntegrationRepositoryInterface $repository,
        private readonly MovieRepositoryInterface $movieRepository,
        private readonly TmdbClientInterface $tmdbClient,
        private readonly TmdbMovieMapper $tmdbMovieMapper,
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
            $logId = $this->safeLogIntegration($requestPayload, $responsePayload, 'success');

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
            $this->safeLogIntegration($requestPayload, [
                'error_code' => $exception->getErrorCode(),
                'message' => $exception->getMessage(),
            ], 'failed');

            throw $exception;
        } catch (Throwable $exception) {
            $this->safeLogIntegration($requestPayload, [
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
    private function safeLogIntegration(array $requestPayload, array $responsePayload, string $status): ?int
    {
        try {
            return $this->repository->logIntegration(
                $this->correlationIdProvider->get(),
                self::SERVICE_TMDB,
                self::OPERATION_TMDB_MOVIE_SYNC,
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
