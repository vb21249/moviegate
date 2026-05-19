<?php

declare(strict_types=1);

namespace App\Modules\Rating\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for ratings.
 *
 * @property int|string $id
 * @property int|string $user_id
 * @property int|string $movie_id
 * @property int|string $score
 * @property string|null $review_text
 * @property string $rated_at
 */
final class RatingRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ratings}}';
    }
}
