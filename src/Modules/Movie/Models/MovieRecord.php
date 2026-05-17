<?php

declare(strict_types=1);

namespace App\Modules\Movie\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for movies.
 *
 * @property int|string $id
 */
final class MovieRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%movies}}';
    }
}