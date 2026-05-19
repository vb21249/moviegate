<?php

declare(strict_types=1);

namespace App\Modules\Comment\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for comments.
 *
 * @property int|string $id
 * @property int|string $user_id
 * @property int|string|null $movie_id
 * @property int|string|null $review_id
 * @property int|string|null $parent_id
 * @property string $body
 * @property int|string $likes_count
 */
final class CommentRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%comments}}';
    }
}
