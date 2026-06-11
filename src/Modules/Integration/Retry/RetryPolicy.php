<?php

declare(strict_types=1);

namespace App\Modules\Integration\Retry;

use App\Modules\Integration\Exceptions\IntegrationException;
use RuntimeException;
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
        $attempts = max(1, $attempts);
        $currentAttempt = 0;
        $lastException = null;

        while ($currentAttempt < $attempts) {
            try {
                return $callback();
            } catch (Throwable $exception) {
                $lastException = $exception;
                ++$currentAttempt;

                if (!$this->shouldRetry($exception) || $currentAttempt >= $attempts) {
                    throw $exception;
                }

                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }
            }
        }

        throw $lastException ?? new RuntimeException('Retry policy failed without exception.');
    }

    private function shouldRetry(Throwable $exception): bool
    {
        if (!$exception instanceof IntegrationException) {
            return true;
        }

        return in_array($exception->getErrorCode(), [
            IntegrationException::CODE_TMDB_REQUEST_FAILED,
            IntegrationException::CODE_OMDB_REQUEST_FAILED,
        ], true);
    }
}
