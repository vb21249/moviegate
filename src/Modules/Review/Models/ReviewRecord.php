<?php

declare(strict_types=1);

namespace App\Modules\Review\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for reviews.
 *
 * @property int|string $id
 * @property int $user_id
 * @property int $movie_id
 * @property int|null $rating_id
 * @property string $title
 * @property string $body
 * @property int $likes_count
 * @property string $status
 * @property string|null $published_at
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 */
final class ReviewRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%reviews}}';
    }
}
