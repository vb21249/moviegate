<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Contracts\CircuitBreakerInterface;
use App\Common\Contracts\HttpClientInterface;
use App\Common\Dto\HttpRequestDto;
use App\Common\Dto\HttpResponseDto;
use App\Modules\Integration\Clients\TmdbClient;
use App\Modules\Integration\Exceptions\IntegrationException;
use App\Modules\Integration\Retry\RetryPolicy;
use Closure;
use PHPUnit\Framework\TestCase;
use Throwable;

final class TmdbClientTest extends TestCase
{
    protected function tearDown(): void
    {
        \Yii::$app = null;

        parent::tearDown();
    }

    public function testMissingCredentialsThrowsConfigErrorWithoutHttpRequestOrOpenCircuit(): void
    {
        $this->setTmdbParams([
            'apiKey' => '',
            'accessToken' => '',
        ]);
        $httpClient = new RecordingHttpClient();
        $circuitBreaker = new RecordingCircuitBreaker();

        try {
            $this->client($httpClient, $circuitBreaker)->movieDetails(335984);
            self::fail('Expected IntegrationException.');
        } catch (IntegrationException $exception) {
            self::assertSame(IntegrationException::CODE_TMDB_CONFIG_MISSING, $exception->getErrorCode());
            self::assertSame(500, $exception->getStatusCode());
        }

        self::assertSame([], $httpClient->requests);
        self::assertFalse($circuitBreaker->opened);
    }

    public function testNotFoundDoesNotRetryOrOpenCircuit(): void
    {
        $this->setTmdbParams(['apiKey' => 'test-key']);
        $httpClient = new RecordingHttpClient(new HttpResponseDto(404, [], ['status_message' => 'Not found']));
        $circuitBreaker = new RecordingCircuitBreaker();

        try {
            $this->client($httpClient, $circuitBreaker)->movieDetails(404);
            self::fail('Expected IntegrationException.');
        } catch (IntegrationException $exception) {
            self::assertSame(IntegrationException::CODE_TMDB_NOT_FOUND, $exception->getErrorCode());
            self::assertSame(404, $exception->getStatusCode());
        }

        self::assertCount(1, $httpClient->requests);
        self::assertFalse($circuitBreaker->opened);
    }

    public function testServerFailureRetriesAndOpensCircuit(): void
    {
        $this->setTmdbParams(['apiKey' => 'test-key']);
        $httpClient = new RecordingHttpClient(
            new HttpResponseDto(500, [], ['status_message' => 'Server error']),
            new HttpResponseDto(500, [], ['status_message' => 'Server error']),
            new HttpResponseDto(500, [], ['status_message' => 'Server error']),
        );
        $circuitBreaker = new RecordingCircuitBreaker();

        try {
            $this->client($httpClient, $circuitBreaker)->movieDetails(335984);
            self::fail('Expected IntegrationException.');
        } catch (IntegrationException $exception) {
            self::assertSame(IntegrationException::CODE_TMDB_REQUEST_FAILED, $exception->getErrorCode());
            self::assertSame(502, $exception->getStatusCode());
        }

        self::assertCount(3, $httpClient->requests);
        self::assertTrue($circuitBreaker->opened);
    }

    public function testSearchUsesApiKeyQueryAuthentication(): void
    {
        $this->setTmdbParams(['apiKey' => 'test-key']);
        $httpClient = new RecordingHttpClient(new HttpResponseDto(200, [], ['results' => []]));

        $this->client($httpClient)->searchMovies('Blade Runner 2049', 'uk-UA', false);

        self::assertCount(1, $httpClient->requests);
        $request = $httpClient->requests[0];
        self::assertSame('GET', $request->method);
        self::assertSame('https://api.example.test/3/search/movie', $request->uri);
        self::assertSame('Blade Runner 2049', $request->query['query']);
        self::assertSame('uk-UA', $request->query['language']);
        self::assertSame('false', $request->query['include_adult']);
        self::assertSame('test-key', $request->query['api_key']);
        self::assertSame([], $request->headers);
    }

    /**
     * @param array<string, string> $params
     */
    private function setTmdbParams(array $params): void
    {
        \Yii::$app = (object) [
            'params' => [
                'tmdb' => $params + [
                    'baseUri' => 'https://api.example.test/3/',
                    'imageBaseUri' => 'https://image.example.test/t/p/original/',
                    'apiKey' => '',
                    'accessToken' => '',
                ],
            ],
        ];
    }

    private function client(
        RecordingHttpClient $httpClient,
        ?RecordingCircuitBreaker $circuitBreaker = null
    ): TmdbClient {
        return new TmdbClient(
            $httpClient,
            $circuitBreaker ?? new RecordingCircuitBreaker(),
            new RetryPolicy()
        );
    }
}

final class RecordingHttpClient implements HttpClientInterface
{
    /**
     * @var list<HttpRequestDto>
     */
    public array $requests = [];

    /**
     * @var list<HttpResponseDto|Throwable>
     */
    private array $responses;

    public function __construct(HttpResponseDto|Throwable ...$responses)
    {
        $this->responses = $responses;
    }

    public function send(HttpRequestDto $request): HttpResponseDto
    {
        $this->requests[] = $request;
        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        return $response ?? new HttpResponseDto(200, [], []);
    }
}

final class RecordingCircuitBreaker implements CircuitBreakerInterface
{
    public bool $opened = false;

    public function call(string $name, Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            $this->opened = true;

            throw $exception;
        }
    }
}
