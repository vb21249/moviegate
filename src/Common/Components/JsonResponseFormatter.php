<?php

declare(strict_types=1);

namespace App\Common\Components;

use yii\web\JsonResponseFormatter as BaseJsonResponseFormatter;

/**
 * Adds default JSON encoding flags for API responses.
 */
final class JsonResponseFormatter extends BaseJsonResponseFormatter
{
    public $encodeOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
}
