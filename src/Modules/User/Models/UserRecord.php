<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for users.
 *
 * @property int|string $id
 */
final class UserRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }
}