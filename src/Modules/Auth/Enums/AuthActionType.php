<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

/**
 * Auth action enum.
 */
enum AuthActionType: string
{
    case PasswordReset = 'password_reset';
    case EmailVerification = 'email_verification';
}
