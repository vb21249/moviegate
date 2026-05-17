<?php

declare(strict_types=1);

namespace App\Common\Validators;

use yii\validators\Validator;

/**
 * Pagination bounds validator.
 */
final class PaginationValidator extends Validator
{
    protected function validateValue($value): ?array
    {
        if (!is_int($value) || $value < 1 || $value > 100) {
            return ['Pagination value must be an integer between 1 and 100.', []];
        }

        return null;
    }
}
