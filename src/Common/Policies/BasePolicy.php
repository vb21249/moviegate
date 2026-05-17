<?php

declare(strict_types=1);

namespace App\Common\Policies;

/**
 * Base policy contract implementation.
 */
abstract class BasePolicy
{
    /**
     * @param array<string, mixed> $context
     */
    public function can(string $ability, array $context = []): bool
    {
        return false;
    }
}
