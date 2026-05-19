<?php

declare(strict_types=1);

namespace App\Modules\Comment\Validators;

use yii\validators\Validator;

/**
 * Comment validator extension point.
 */
final class CommentValidator extends Validator
{
    protected function validateValue($value): ?array
    {
        return null;
    }
}
