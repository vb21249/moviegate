<?php

declare(strict_types=1);

namespace App\Common\Contracts;

/**
 * Correlation ID provider for tracing.
 */
interface CorrelationIdProviderInterface
{
    /**
     * Initializes current request correlation ID.
     */
    public function initialize(): void;

    /**
     * @return string
     */
    public function get(): string;
}
