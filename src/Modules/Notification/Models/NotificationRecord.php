<?php

declare(strict_types=1);

namespace App\Modules\Notification\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for notifications.
 *
 * @property int|string $id
 */
final class NotificationRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%notifications}}';
    }
}