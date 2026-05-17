<?php

declare(strict_types=1);

namespace App\Common\Logging;

use Monolog\Level;
use yii\log\Logger;
use yii\log\Target;

/**
 * Bridges Yii log messages to Monolog.
 */
final class MonologTarget extends Target
{
    /**
     * @inheritDoc
     */
    public function export(): void
    {
        foreach ($this->messages as [$message, $level, $category, $timestamp]) {
            MonologFactory::make($category)->log($this->mapLevel((int) $level), (string) $message, [
                'category' => $category,
                'timestamp' => $timestamp,
            ]);
        }
    }

    /**
     * @param int $yiiLevel
     */
    private function mapLevel(int $yiiLevel): Level
    {
        return match ($yiiLevel) {
            Logger::LEVEL_ERROR => Level::Error,
            Logger::LEVEL_WARNING => Level::Warning,
            Logger::LEVEL_INFO => Level::Info,
            default => Level::Debug,
        };
    }
}
