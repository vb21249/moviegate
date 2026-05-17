<?php

declare(strict_types=1);

namespace App\Modules\Common\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for api_logs.
 *
 * @property int|string $id
 */
final class CommonRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%api_logs}}';
    }
}