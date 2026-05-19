<?php

declare(strict_types=1);

namespace App\Modules\User\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * User module exception.
 */
final class UserException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_USER_NOT_FOUND = 'user_not_found';
}
