<?php

declare(strict_types=1);

namespace App\Modules\Rating\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for ratings.
 *
 * @property int|string $id
 */
final class RatingRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ratings}}';
    }
}