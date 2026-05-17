<?php

declare(strict_types=1);

namespace App\Commands;

use yii\console\Controller;

/**
 * Console health check helpers.
 */
final class HealthController extends Controller
{
    /**
     * @return int
     */
    public function actionIndex(): int
    {
        $this->stdout("ok\n");

        return self::EXIT_CODE_NORMAL;
    }
}
