<?php

declare(strict_types=1);

namespace App\Common\Queue;

use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Base queue job.
 */
abstract class BaseJob extends BaseObject implements JobInterface
{
}
