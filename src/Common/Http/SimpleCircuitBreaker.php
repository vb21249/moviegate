<?php

declare(strict_types=1);

namespace App\Common\Http;

use App\Common\Contracts\CacheInterface;
use App\Common\Contracts\CircuitBreakerInterface;
use Closure;
use RuntimeException;
use Throwable;

/**
 * Minimal cache-backed circuit breaker foundation.
 */
final class SimpleCircuitBreaker implements CircuitBreakerInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @template T
     *
     * @param string $name
     * @param Closure():T $callback
     *
     * @return T
     */
    public function call(string $name, Closure $callback): mixed
    {
        if ($this->cache->get($this->key($name)) === 'open') {
            throw new RuntimeException(sprintf('Circuit "%s" is open.', $name));
        }

        try {
            return $callback();
        } catch (Throwable $exception) {
            $this->cache->set($this->key($name), 'open', 30);

            throw $exception;
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function key(string $name): string
    {
        return sprintf('circuit_breaker:%s', $name);
    }
}
