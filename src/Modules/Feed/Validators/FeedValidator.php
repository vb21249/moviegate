<?php

declare(strict_types=1);

namespace App\Modules\Feed\Validators;

use yii\validators\Validator;

/**
 * Feed validator extension point.
 */
final class FeedValidator extends Validator
{
    protected function validateValue($value): ?array
    {
        return null;
    }
}
