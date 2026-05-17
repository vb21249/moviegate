<?php

declare(strict_types=1);

namespace App\Modules\Feed\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for feed_events.
 *
 * @property int|string $id
 */
final class FeedRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%feed_events}}';
    }
}