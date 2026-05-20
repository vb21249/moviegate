<?php

declare(strict_types=1);

namespace App\Modules\Notification\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for notifications.
 *
 * @property int|string $id
 * @property int|string $user_id
 * @property string $type
 * @property array|string $payload_json
 * @property string|null $read_at
 */
final class NotificationRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%notifications}}';
    }
}
