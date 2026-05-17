<?php

declare(strict_types=1);

namespace App\Common\Contracts;

use Closure;

/**
 * Circuit breaker contract for remote calls.
 */
interface CircuitBreakerInterface
{
    /**
     * @template T
     *
     * @param string $name
     * @param Closure():T $callback
     *
     * @return T
     */
    public function call(string $name, Closure $callback): mixed;
}
