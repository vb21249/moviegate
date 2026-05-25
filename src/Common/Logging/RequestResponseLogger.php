<?php

declare(strict_types=1);

namespace App\Common\Logging;

use App\Common\Contracts\ApiLogWriterInterface;
use App\Common\Contracts\CorrelationIdProviderInterface;
use App\Common\Contracts\RequestResponseLoggerInterface;
use Monolog\Logger;
use Throwable;
use yii\web\Request;
use yii\web\Response;

/**
 * Request/response logger backed by Monolog and api_logs persistence.
 */
final class RequestResponseLogger implements RequestResponseLoggerInterface
{
    private const REDACTED_VALUE = '[redacted]';
    private const SENSITIVE_KEYS = [
        'accesstoken',
        'apikey',
        'authorization',
        'clientsecret',
        'password',
        'passwordconfirmation',
        'passwordhash',
        'refreshtoken',
        'secret',
        'token',
    ];

    private Logger $requestLogger;
    private Logger $responseLogger;

    /**
     * @var array{method: string, uri: string, body: mixed}|null
     */
    private ?array $requestContext = null;

    public function __construct(
        private readonly CorrelationIdProviderInterface $correlationIdProvider,
        private readonly ApiLogWriterInterface $apiLogWriter,
        ?Logger $requestLogger = null,
        ?Logger $responseLogger = null,
    ) {
        $this->requestLogger = $requestLogger ?? MonologFactory::make('request');
        $this->responseLogger = $responseLogger ?? MonologFactory::make('response');
    }

    public function logRequest(Request $request): void
    {
        $this->requestContext = $this->requestContext($request);

        $this->requestLogger->info('Incoming request', [
            'correlation_id' => $this->correlationIdProvider->get(),
            'method' => $this->requestContext['method'],
            'path' => $this->requestContext['uri'],
            'body' => $this->requestContext['body'],
        ]);
    }

    public function logResponse(Response $response): void
    {
        $responseBody = $this->responseBody($response);

        $this->responseLogger->info('Outgoing response', [
            'correlation_id' => $this->correlationIdProvider->get(),
            'status_code' => $response->statusCode,
            'data' => $responseBody,
        ]);

        if ($this->requestContext === null) {
            return;
        }

        try {
            $this->apiLogWriter->write(
                $this->correlationIdProvider->get(),
                $this->requestContext['method'],
                $this->requestContext['uri'],
                $this->requestContext['body'],
                $response->statusCode,
                $responseBody
            );
        } catch (Throwable $exception) {
            $this->responseLogger->warning('API log persistence failed', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array{method: string, uri: string, body: mixed}
     */
    private function requestContext(Request $request): array
    {
        return [
            'method' => $request->getMethod(),
            'uri' => $request->getUrl(),
            'body' => $this->requestBody($request),
        ];
    }

    private function requestBody(Request $request): mixed
    {
        try {
            $body = $request->getBodyParams();
        } catch (Throwable) {
            $rawBody = trim($request->getRawBody());

            return $rawBody !== '' ? ['raw' => '[unparsed request body]'] : null;
        }

        return $body !== [] ? $this->redact($body) : null;
    }

    private function responseBody(Response $response): mixed
    {
        if ($response->data !== null) {
            return $this->redact($response->data);
        }

        if ($response->content === null || $response->content === '') {
            return null;
        }

        $decoded = json_decode($response->content, true);

        return json_last_error() === JSON_ERROR_NONE ? $this->redact($decoded) : $response->content;
    }

    private function redact(mixed $payload): mixed
    {
        if (!is_array($payload)) {
            return $payload;
        }

        $redacted = [];

        foreach ($payload as $key => $value) {
            $redacted[$key] = $this->isSensitiveKey($key) ? self::REDACTED_VALUE : $this->redact($value);
        }

        return $redacted;
    }

    private function isSensitiveKey(int|string $key): bool
    {
        $normalized = strtolower(str_replace(['-', '_'], '', (string) $key));

        return in_array($normalized, self::SENSITIVE_KEYS, true);
    }
}
