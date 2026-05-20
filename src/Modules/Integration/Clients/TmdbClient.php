<?php

declare(strict_types=1);

namespace App\Modules\Integration\Clients;

use App\Common\Contracts\CircuitBreakerInterface;
use App\Common\Contracts\HttpClientInterface;
use App\Common\Dto\HttpRequestDto;
use App\Common\Dto\HttpResponseDto;
use App\Modules\Integration\Exceptions\IntegrationException;
use App\Modules\Integration\Retry\RetryPolicy;
use Yii;

/**
 * TMDB client adapter with circuit breaker.
 */
final class TmdbClient implements TmdbClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CircuitBreakerInterface $circuitBreaker,
        private readonly RetryPolicy $retryPolicy,
    ) {
    }

    public function movieDetails(int $tmdbId, string $language = 'en-US'): array
    {
        return $this->get(sprintf('movie/%d', $tmdbId), ['language' => $language]);
    }

    public function searchMovies(string $query, string $language = 'en-US', bool $includeAdult = false): array
    {
        return $this->get('search/movie', [
            'query' => $query,
            'language' => $language,
            'include_adult' => $includeAdult ? 'true' : 'false',
            'page' => 1,
        ]);
    }

    public function get(string $resource, array $query = []): array
    {
        $result = $this->circuitBreaker->call(
            'tmdb',
            function () use ($resource, $query): array|IntegrationException {
                try {
                    return $this->retryPolicy->execute(
                        fn (): array => $this->sendGet($resource, $query)
                    );
                } catch (IntegrationException $exception) {
                    if ($this->shouldBypassCircuitBreaker($exception)) {
                        return $exception;
                    }

                    throw $exception;
                }
            }
        );

        if ($result instanceof IntegrationException) {
            throw $result;
        }

        return $result;
    }

    /**
     * @param string $resource
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function sendGet(string $resource, array $query = []): array
    {
        $response = $this->httpClient->send(new HttpRequestDto(
            method: 'GET',
            uri: rtrim((string) Yii::$app->params['tmdb']['baseUri'], '/') . '/' . ltrim($resource, '/'),
            headers: $this->authHeaders(),
            query: $this->authQuery($query)
        ));

        $this->assertSuccessfulResponse($response);

        if (!is_array($response->body)) {
            throw new IntegrationException(
                'TMDB returned an invalid response.',
                502,
                IntegrationException::CODE_TMDB_REQUEST_FAILED
            );
        }

        return $response->body;
    }

    /**
     * @return array<string, string>
     */
    private function authHeaders(): array
    {
        $accessToken = trim((string) (Yii::$app->params['tmdb']['accessToken'] ?? ''));

        return $accessToken !== '' ? ['Authorization' => 'Bearer ' . $accessToken] : [];
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    private function authQuery(array $query): array
    {
        if ($this->authHeaders() !== []) {
            return $query;
        }

        $apiKey = trim((string) (Yii::$app->params['tmdb']['apiKey'] ?? ''));

        if ($apiKey === '') {
            throw new IntegrationException(
                'TMDB credentials are not configured.',
                500,
                IntegrationException::CODE_TMDB_CONFIG_MISSING
            );
        }

        return $query + ['api_key' => $apiKey];
    }

    private function assertSuccessfulResponse(HttpResponseDto $response): void
    {
        if ($response->statusCode === 404) {
            throw new IntegrationException(
                'TMDB movie not found.',
                404,
                IntegrationException::CODE_TMDB_NOT_FOUND
            );
        }

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw new IntegrationException(
                'TMDB request failed.',
                502,
                IntegrationException::CODE_TMDB_REQUEST_FAILED
            );
        }
    }

    private function shouldBypassCircuitBreaker(IntegrationException $exception): bool
    {
        return in_array($exception->getErrorCode(), [
            IntegrationException::CODE_TMDB_CONFIG_MISSING,
            IntegrationException::CODE_TMDB_NOT_FOUND,
        ], true);
    }
}
