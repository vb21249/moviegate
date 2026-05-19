<?php

declare(strict_types=1);

namespace App\Modules\Rating\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Rating module exception.
 */
final class RatingException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_RATING_NOT_FOUND = 'rating_not_found';
    public const CODE_MOVIE_NOT_FOUND = 'movie_not_found';
    public const CODE_RATING_CREATE_FAILED = 'rating_create_failed';
}
