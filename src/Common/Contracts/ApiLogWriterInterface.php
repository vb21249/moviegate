<?php

declare(strict_types=1);

namespace App\Common\Contracts;

/**
 * Persists completed HTTP request/response log entries.
 */
interface ApiLogWriterInterface
{
    public function write(
        string $correlationId,
        string $requestMethod,
        string $requestUri,
        mixed $requestBody,
        int $responseStatus,
        mixed $responseBody
    ): void;
}
