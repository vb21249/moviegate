<?php

declare(strict_types=1);

namespace App\Common\Enums;

/**
 * Global feature flag keys.
 */
enum FeatureFlag: string
{
    case Registration = 'registration';
    case Recommendations = 'recommendations';
}
