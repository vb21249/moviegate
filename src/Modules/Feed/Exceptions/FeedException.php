<?php

declare(strict_types=1);

namespace App\Modules\Feed\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Feed module exception.
 */
final class FeedException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_FEED_EVENT_NOT_FOUND = 'feed_event_not_found';
    public const CODE_FEED_EVENT_CREATE_FAILED = 'feed_event_create_failed';
}
