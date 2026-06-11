<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Contracts\ApiLogWriterInterface;
use App\Common\Contracts\CorrelationIdProviderInterface;
use App\Common\Logging\RequestResponseLogger;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use yii\web\Request;
use yii\web\Response;

final class RequestResponseLoggerTest extends TestCase
{
    public function testWritesCompletedRequestResponseToApiLogWriter(): void
    {
        $writer = new InMemoryApiLogWriter();
        $logger = new RequestResponseLogger(
            new FixedCorrelationIdProvider('test-correlation-id'),
            $writer,
            $this->nullLogger('request'),
            $this->nullLogger('response')
        );
        $request = $this->request('/api/v1/movies?limit=1', ['title' => 'Blade Runner']);
        $response = $this->response(201, ['id' => 42, 'created' => true]);

        $this->withRequestMethod('POST', function () use ($logger, $request, $response): void {
            $logger->logRequest($request);
            $logger->logResponse($response);
        });

        self::assertCount(1, $writer->entries);
        self::assertSame('test-correlation-id', $writer->entries[0]['correlation_id']);
        self::assertSame('POST', $writer->entries[0]['request_method']);
        self::assertSame('/api/v1/movies?limit=1', $writer->entries[0]['request_uri']);
        self::assertSame(['title' => 'Blade Runner'], $writer->entries[0]['request_body']);
        self::assertSame(201, $writer->entries[0]['response_status']);
        self::assertSame(['id' => 42, 'created' => true], $writer->entries[0]['response_body']);
    }

    public function testApiLogWriterFailureDoesNotBreakResponseLogging(): void
    {
        $logger = new RequestResponseLogger(
            new FixedCorrelationIdProvider('test-correlation-id'),
            new FailingApiLogWriter(),
            $this->nullLogger('request'),
            $this->nullLogger('response')
        );
        $request = $this->request('/health', []);
        $response = $this->response(200, ['status' => 'ok']);

        $this->withRequestMethod('GET', function () use ($logger, $request, $response): void {
            $logger->logRequest($request);
            $logger->logResponse($response);
        });

        self::assertTrue(true);
    }

    public function testRedactsSensitiveRequestAndResponseFieldsBeforePersisting(): void
    {
        $writer = new InMemoryApiLogWriter();
        $logger = new RequestResponseLogger(
            new FixedCorrelationIdProvider('test-correlation-id'),
            $writer,
            $this->nullLogger('request'),
            $this->nullLogger('response')
        );
        $request = $this->request('/api/v1/auth/login', [
            'email' => 'user@example.test',
            'password' => 'plain-secret',
        ]);
        $response = $this->response(200, [
            'access_token' => 'jwt-token',
            'refresh_token' => 'refresh-token',
            'user' => ['id' => 1],
        ]);

        $this->withRequestMethod('POST', function () use ($logger, $request, $response): void {
            $logger->logRequest($request);
            $logger->logResponse($response);
        });

        self::assertSame('[redacted]', $writer->entries[0]['request_body']['password']);
        self::assertSame('[redacted]', $writer->entries[0]['response_body']['access_token']);
        self::assertSame('[redacted]', $writer->entries[0]['response_body']['refresh_token']);
        self::assertSame(['id' => 1], $writer->entries[0]['response_body']['user']);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function request(string $uri, array $body): Request
    {
        $request = new Request();
        $request->setUrl($uri);
        $request->setBodyParams($body);

        return $request;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function response(int $statusCode, array $data): Response
    {
        $response = new Response([
            'charset' => 'UTF-8',
        ]);
        $response->setStatusCode($statusCode);
        $response->data = $data;

        return $response;
    }

    private function withoutLoggerOutput(callable $callback): void
    {
        ob_start();

        try {
            $callback();
        } finally {
            ob_end_clean();
        }
    }

    private function withRequestMethod(string $method, callable $callback): void
    {
        $previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = $method;

        try {
            $this->withoutLoggerOutput($callback);
        } finally {
            if ($previousMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $previousMethod;
            }
        }
    }

    private function nullLogger(string $channel): Logger
    {
        $logger = new Logger($channel);
        $logger->pushHandler(new NullHandler());

        return $logger;
    }
}

final class FixedCorrelationIdProvider implements CorrelationIdProviderInterface
{
    public function __construct(
        private readonly string $correlationId,
    ) {
    }

    public function initialize(): void
    {
    }

    public function get(): string
    {
        return $this->correlationId;
    }
}

final class InMemoryApiLogWriter implements ApiLogWriterInterface
{
    /**
     * @var list<array{
     *     correlation_id: string,
     *     request_method: string,
     *     request_uri: string,
     *     request_body: mixed,
     *     response_status: int,
     *     response_body: mixed
     * }>
     */
    public array $entries = [];

    public function write(
        string $correlationId,
        string $requestMethod,
        string $requestUri,
        mixed $requestBody,
        int $responseStatus,
        mixed $responseBody
    ): void {
        $this->entries[] = [
            'correlation_id' => $correlationId,
            'request_method' => $requestMethod,
            'request_uri' => $requestUri,
            'request_body' => $requestBody,
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
        ];
    }
}

final class FailingApiLogWriter implements ApiLogWriterInterface
{
    public function write(
        string $correlationId,
        string $requestMethod,
        string $requestUri,
        mixed $requestBody,
        int $responseStatus,
        mixed $responseBody
    ): void {
        throw new RuntimeException('DB is unavailable.');
    }
}
