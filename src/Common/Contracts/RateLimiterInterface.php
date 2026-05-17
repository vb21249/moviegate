<?php

declare(strict_types=1);

namespace App\Common\Contracts;

/**
 * Rate limiter abstraction.
 */
interface RateLimiterInterface
{
    /**
     * @param string $key
     * @param int $limit
     * @param int $period
     */
    public function allow(string $key, int $limit, int $period): bool;
}
