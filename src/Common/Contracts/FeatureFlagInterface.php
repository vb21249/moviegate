<?php

declare(strict_types=1);

namespace App\Common\Contracts;

/**
 * Feature flag service contract.
 */
interface FeatureFlagInterface
{
    /**
     * @param string $feature
     */
    public function isEnabled(string $feature): bool;
}
