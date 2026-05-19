<?php

declare(strict_types=1);

namespace App\Modules\Rating\Validators;

use yii\validators\Validator;

/**
 * Rating validator extension point.
 */
final class RatingValidator extends Validator
{
    protected function validateValue($value): ?array
    {
        return null;
    }
}
