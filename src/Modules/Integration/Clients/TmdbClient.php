<?php

declare(strict_types=1);

namespace App\Modules\Integration\Clients;

use App\Common\Contracts\CircuitBreakerInterface;
use App\Common\Contracts\HttpClientInterface;
use App\Common\Dto\HttpRequestDto;
use Yii;

/**
 * TMDB client adapter with circuit breaker.
 */
final class TmdbClient implements TmdbClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CircuitBreakerInterface $circuitBreaker,
    ) {
    }

    public function get(string $resource, array $query = []): array
    {
        return $this->circuitBreaker->call('tmdb', function () use ($resource, $query): array {
            $response = $this->httpClient->send(new HttpRequestDto(
                method: 'GET',
                uri: rtrim((string) Yii::$app->params['tmdb']['baseUri'], '/') . '/' . ltrim($resource, '/'),
                query: $query + ['api_key' => Yii::$app->params['tmdb']['apiKey']]
            ));

            return is_array($response->body) ? $response->body : [];
        });
    }
}
