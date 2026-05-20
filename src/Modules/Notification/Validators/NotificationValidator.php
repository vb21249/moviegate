<?php

declare(strict_types=1);

namespace App\Modules\Notification\Validators;

use yii\validators\Validator;

/**
 * Notification validator extension point.
 */
final class NotificationValidator extends Validator
{
    protected function validateValue($value): ?array
    {
        return null;
    }
}
