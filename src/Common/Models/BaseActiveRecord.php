<?php

declare(strict_types=1);

namespace App\Common\Models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Base ActiveRecord with timestamps.
 */
abstract class BaseActiveRecord extends ActiveRecord
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}
