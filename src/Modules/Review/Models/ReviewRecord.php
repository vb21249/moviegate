<?php

declare(strict_types=1);

namespace App\Modules\Review\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for reviews.
 *
 * @property int|string $id
 */
final class ReviewRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%reviews}}';
    }
}