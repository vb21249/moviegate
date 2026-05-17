<?php

declare(strict_types=1);

namespace App\Common\Exceptions;

use RuntimeException;

/**
 * Base API exception with explicit status code.
 */
class ApiException extends RuntimeException
{
    /**
     * @param string $message
     * @param int $statusCode
     * @param string $errorCode
     */
    public function __construct(
        string $message,
        private readonly int $statusCode = 400,
        private readonly string $errorCode = 'api_error',
    ) {
        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
