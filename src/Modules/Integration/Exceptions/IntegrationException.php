<?php

declare(strict_types=1);

namespace App\Modules\Integration\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Integration module exception.
 */
final class IntegrationException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_TMDB_CONFIG_MISSING = 'tmdb_config_missing';
    public const CODE_TMDB_NOT_FOUND = 'tmdb_not_found';
    public const CODE_TMDB_REQUEST_FAILED = 'tmdb_request_failed';
    public const CODE_OMDB_CONFIG_MISSING = 'omdb_config_missing';
    public const CODE_OMDB_NOT_FOUND = 'omdb_not_found';
    public const CODE_OMDB_REQUEST_FAILED = 'omdb_request_failed';
}
