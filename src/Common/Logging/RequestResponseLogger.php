<?php

declare(strict_types=1);

namespace App\Common\Logging;

use App\Common\Contracts\CorrelationIdProviderInterface;
use App\Common\Contracts\RequestResponseLoggerInterface;
use Monolog\Logger;
use yii\web\Request;
use yii\web\Response;

/**
 * Monolog-based request/response logger.
 */
final class RequestResponseLogger implements RequestResponseLoggerInterface
{
    private Logger $requestLogger;
    private Logger $integrationLogger;

    public function __construct(
        private readonly CorrelationIdProviderInterface $correlationIdProvider,
    ) {
        $this->requestLogger = MonologFactory::make('request');
        $this->integrationLogger = MonologFactory::make('response');
    }

    public function logRequest(Request $request): void
    {
        $this->requestLogger->info('Incoming request', [
            'correlation_id' => $this->correlationIdProvider->get(),
            'method' => $request->getMethod(),
            'path' => $request->getUrl(),
            'body' => $request->getBodyParams(),
        ]);
    }

    public function logResponse(Response $response): void
    {
        $this->integrationLogger->info('Outgoing response', [
            'correlation_id' => $this->correlationIdProvider->get(),
            'status_code' => $response->statusCode,
            'data' => $response->data,
        ]);
    }
}
