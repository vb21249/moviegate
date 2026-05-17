<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for recommendation_cache.
 *
 * @property int|string $id
 */
final class RecommendationRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%recommendation_cache}}';
    }
}