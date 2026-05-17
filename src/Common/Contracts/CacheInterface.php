<?php

declare(strict_types=1);

namespace App\Common\Contracts;

/**
 * Cache abstraction.
 */
interface CacheInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void;

    /**
     * @param string $key
     */
    public function delete(string $key): void;
}
