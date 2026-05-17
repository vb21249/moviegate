<?php

declare(strict_types=1);

namespace App\Common\Services;

use App\Common\Contracts\FeatureFlagInterface;
use Yii;

/**
 * Env-configured feature flag storage.
 */
final class ArrayFeatureFlagService implements FeatureFlagInterface
{
    public function isEnabled(string $feature): bool
    {
        return (bool) (Yii::$app->params['features'][$feature] ?? false);
    }
}
