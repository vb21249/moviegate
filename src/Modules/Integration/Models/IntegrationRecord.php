<?php

declare(strict_types=1);

namespace App\Modules\Integration\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for integration_logs.
 *
 * @property int|string $id
 */
final class IntegrationRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%integration_logs}}';
    }
}