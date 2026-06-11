<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Contracts\CircuitBreakerInterface;
use App\Common\Contracts\HttpClientInterface;
use App\Common\Dto\HttpRequestDto;
use App\Common\Dto\HttpResponseDto;
use App\Modules\Integration\Clients\OmdbClient;
use App\Modules\Integration\Exceptions\IntegrationException;
use App\Modules\Integration\Retry\RetryPolicy;
use Closure;
use PHPUnit\Framework\TestCase;
use Throwable;

final class OmdbClientTest extends TestCase
{
    protected function tearDown(): void
    {
        \Yii::$app = null;

        parent::tearDown();
    }

    public function testMissingCredentialsThrowsConfigErrorWithoutHttpRequestOrOpenCircuit(): void
    {
        $this->setOmdbParams(['apiKey' => '']);
        $httpClient = new OmdbRecordingHttpClient();
        $circuitBreaker = new OmdbRecordingCircuitBreaker();

        try {
            $this->client($httpClient, $circuitBreaker)->movieDetails('tt1856101');
            self::fail('Expected IntegrationException.');
        } catch (IntegrationException $exception) {
            self::assertSame(IntegrationException::CODE_OMDB_CONFIG_MISSING, $exception->getErrorCode());
            self::assertSame(500, $exception->getStatusCode());
        }

        self::assertSame([], $httpClient->requests);
        self::assertFalse($circuitBreaker->opened);
    }

    public function testNotFoundBodyDoesNotRetryOrOpenCircuit(): void
    {
        $this->setOmdbParams(['apiKey' => 'test-key']);
        $httpClient = new OmdbRecordingHttpClient(new HttpResponseDto(200, [], [
            'Response' => 'False',
            'Error' => 'Movie not found!',
        ]));
        $circuitBreaker = new OmdbRecordingCircuitBreaker();

        try {
            $this->client($httpClient, $circuitBreaker)->movieDetails('tt0000000');
            self::fail('Expected IntegrationException.');
        } catch (IntegrationException $exception) {
            self::assertSame(IntegrationException::CODE_OMDB_NOT_FOUND, $exception->getErrorCode());
            self::assertSame(404, $exception->getStatusCode());
        }

        self::assertCount(1, $httpClient->requests);
        self::assertFalse($circuitBreaker->opened);
    }

    public function testSearchUsesApiKeyQueryAuthentication(): void
    {
        $this->setOmdbParams(['apiKey' => 'test-key']);
        $httpClient = new OmdbRecordingHttpClient(new HttpResponseDto(200, [], [
            'Response' => 'True',
            'Search' => [],
        ]));

        $this->client($httpClient)->searchMovies('Blade Runner 2049');

        self::assertCount(1, $httpClient->requests);
        $request = $httpClient->requests[0];
        self::assertSame('GET', $request->method);
        self::assertSame('https://omdb.example.test/', $request->uri);
        self::assertSame('Blade Runner 2049', $request->query['s']);
        self::assertSame('movie', $request->query['type']);
        self::assertSame(1, $request->query['page']);
        self::assertSame('test-key', $request->query['apikey']);
    }

    public function testServerFailureRetriesAndOpensCircuit(): void
    {
        $this->setOmdbParams(['apiKey' => 'test-key']);
        $httpClient = new OmdbRecordingHttpClient(
            new HttpResponseDto(500, [], ['Error' => 'Server error']),
            new HttpResponseDto(500, [], ['Error' => 'Server error']),
            new HttpResponseDto(500, [], ['Error' => 'Server error']),
        );
        $circuitBreaker = new OmdbRecordingCircuitBreaker();

        try {
            $this->client($httpClient, $circuitBreaker)->movieDetails('tt1856101');
            self::fail('Expected IntegrationException.');
        } catch (IntegrationException $exception) {
            self::assertSame(IntegrationException::CODE_OMDB_REQUEST_FAILED, $exception->getErrorCode());
            self::assertSame(502, $exception->getStatusCode());
        }

        self::assertCount(3, $httpClient->requests);
        self::assertTrue($circuitBreaker->opened);
    }

    /**
     * @param array<string, string> $params
     */
    private function setOmdbParams(array $params): void
    {
        \Yii::$app = (object) [
            'params' => [
                'omdb' => $params + [
                    'baseUri' => 'https://omdb.example.test/',
                    'apiKey' => '',
                ],
            ],
        ];
    }

    private function client(
        OmdbRecordingHttpClient $httpClient,
        ?OmdbRecordingCircuitBreaker $circuitBreaker = null
    ): OmdbClient {
        return new OmdbClient(
            $httpClient,
            $circuitBreaker ?? new OmdbRecordingCircuitBreaker(),
            new RetryPolicy()
        );
    }
}

final class OmdbRecordingHttpClient implements HttpClientInterface
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

final class OmdbRecordingCircuitBreaker implements CircuitBreakerInterface
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
