<?php

declare(strict_types=1);

namespace App\Modules\Notification\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Notification module exception.
 */
final class NotificationException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_NOTIFICATION_NOT_FOUND = 'notification_not_found';
    public const CODE_NOTIFICATION_CREATE_FAILED = 'notification_create_failed';
}
