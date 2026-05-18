<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Auth module exception.
 */
final class AuthException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_EMAIL_ALREADY_EXISTS = 'email_already_exists';
    public const CODE_USERNAME_ALREADY_EXISTS = 'username_already_exists';
    public const CODE_INVALID_CREDENTIALS = 'invalid_credentials';
    public const CODE_SESSION_NOT_FOUND = 'session_not_found';
    public const CODE_INVALID_REFRESH_TOKEN = 'invalid_refresh_token';
    public const CODE_USER_NOT_FOUND = 'user_not_found';
    public const CODE_INVALID_TOKEN = 'invalid_token';
    public const CODE_INVALID_TOKEN_TYPE = 'invalid_token_type';
    public const CODE_USER_CREATE_FAILED = 'user_create_failed';
    public const CODE_SESSION_CREATE_FAILED = 'session_create_failed';
}
