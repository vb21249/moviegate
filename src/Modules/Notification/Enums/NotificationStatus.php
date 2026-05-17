<?php

declare(strict_types=1);

namespace App\Modules\Notification\Enums;

/**
 * Notification status enum.
 */
enum NotificationStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}