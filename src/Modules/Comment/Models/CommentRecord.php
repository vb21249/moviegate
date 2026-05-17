<?php

declare(strict_types=1);

namespace App\Modules\Comment\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for comments.
 *
 * @property int|string $id
 */
final class CommentRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%comments}}';
    }
}