<?php

declare(strict_types=1);

namespace App\Common\Services;

use App\Common\Contracts\RateLimiterInterface;
use yii\redis\Connection;
use Yii;

/**
 * Redis-backed rate limiter foundation.
 */
final class RedisRateLimiter implements RateLimiterInterface
{
    public function allow(string $key, int $limit, int $period): bool
    {
        $counterKey = sprintf('rate_limit:%s', $key);
        $count = (int) $this->redis()->executeCommand('INCR', [$counterKey]);

        if ($count === 1) {
            $this->redis()->executeCommand('EXPIRE', [$counterKey, $period]);
        }

        return $count <= $limit;
    }

    /**
     * @return Connection
     */
    private function redis(): Connection
    {
        /** @var Connection $redis */
        $redis = Yii::$app->redis;

        return $redis;
    }
}
