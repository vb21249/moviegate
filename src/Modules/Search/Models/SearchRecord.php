<?php

declare(strict_types=1);

namespace App\Modules\Search\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for movies.
 *
 * @property int|string $id
 */
final class SearchRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%movies}}';
    }
}