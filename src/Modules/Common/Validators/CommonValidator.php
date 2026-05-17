<?php

declare(strict_types=1);

namespace App\Modules\Common\Validators;

use yii\validators\Validator;

/**
 * Common validator placeholder.
 */
final class CommonValidator extends Validator
{
    protected function validateValue($value): ?array
    {
        return null;
    }
}