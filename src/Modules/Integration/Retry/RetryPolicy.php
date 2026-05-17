<?php

declare(strict_types=1);

namespace App\Modules\Integration\Retry;

use Throwable;

/**
 * Minimal retry policy foundation.
 */
final class RetryPolicy
{
    /**
     * @template T
     *
     * @param callable():T $callback
     *
     * @return T
     */
    public function execute(callable $callback, int $attempts = 3, int $delayMs = 100): mixed
    {
        $currentAttempt = 0;
        $lastException = null;

        while ($currentAttempt < $attempts) {
            try {
                return $callback();
            } catch (Throwable $exception) {
                $lastException = $exception;
                ++$currentAttempt;
                usleep($delayMs * 1000);
            }
        }

        throw $lastException ?? new \RuntimeException('Retry policy failed without exception.');
    }
}
