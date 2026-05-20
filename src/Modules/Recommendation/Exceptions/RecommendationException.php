<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Recommendation module exception.
 */
final class RecommendationException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_RECOMMENDATION_UNAVAILABLE = 'recommendation_unavailable';
}
