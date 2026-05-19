<?php

declare(strict_types=1);

namespace App\Modules\Feed\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for feed_events.
 *
 * @property int|string $id
 * @property int|string $user_id
 * @property string $event_type
 * @property string $entity_type
 * @property int|string|null $entity_id
 * @property array|string|null $payload_json
 * @property string $occurred_at
 */
final class FeedRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%feed_events}}';
    }
}
