<?php

declare(strict_types=1);

namespace App\Modules\Review\Validators;

use yii\validators\Validator;

/**
 * Review validator extension point.
 */
final class ReviewValidator extends Validator
{
    protected function validateValue($value): ?array
    {
        return null;
    }
}
