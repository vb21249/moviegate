<?php

declare(strict_types=1);

namespace App\Modules\Review\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Review module exception.
 */
final class ReviewException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_REVIEW_NOT_FOUND = 'review_not_found';
    public const CODE_MOVIE_NOT_FOUND = 'movie_not_found';
    public const CODE_RATING_NOT_FOUND = 'rating_not_found';
    public const CODE_REVIEW_CREATE_FAILED = 'review_create_failed';
}
