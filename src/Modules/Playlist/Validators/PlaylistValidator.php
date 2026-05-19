<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Validators;

use yii\validators\Validator;

/**
 * Playlist validator extension point.
 */
final class PlaylistValidator extends Validator
{
    protected function validateValue($value): ?array
    {
        return null;
    }
}
