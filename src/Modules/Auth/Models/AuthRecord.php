<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for user_sessions.
 *
 * @property int|string $id
 */
final class AuthRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user_sessions}}';
    }
}