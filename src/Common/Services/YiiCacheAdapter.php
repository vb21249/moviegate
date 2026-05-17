<?php

declare(strict_types=1);

namespace App\Common\Services;

use App\Common\Contracts\CacheInterface;
use yii\caching\CacheInterface as YiiCacheInterface;
use Yii;

/**
 * Adapter for Yii cache component.
 */
final class YiiCacheAdapter implements CacheInterface
{
    public function get(string $key): mixed
    {
        return $this->cache()->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->cache()->set($key, $value, $ttl);
    }

    public function delete(string $key): void
    {
        $this->cache()->delete($key);
    }

    /**
     * @return YiiCacheInterface
     */
    private function cache(): YiiCacheInterface
    {
        /** @var YiiCacheInterface $cache */
        $cache = Yii::$app->cache;

        return $cache;
    }
}
