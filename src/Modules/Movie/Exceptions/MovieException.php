<?php

declare(strict_types=1);

namespace App\Modules\Movie\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Movie module exception.
 */
final class MovieException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_MOVIE_NOT_FOUND = 'movie_not_found';
}
