<?php

declare(strict_types=1);

namespace App\Common\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use RuntimeException;
use Yii;

/**
 * Produces named Monolog channels.
 */
final class MonologFactory
{
    private const STDOUT_CHANNELS = [ 'request', 'response' ];

    /**
     * @param string $channel
     */
    public static function make(string $channel): Logger
    {
        $logger = new Logger($channel);

        if (self::shouldWriteToStdout($channel)) {
            $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));

            return $logger;
        }

        $logger->pushHandler(new RotatingFileHandler(
            self::logDirectory() . '/' . self::fileName($channel) . '.log',
            14,
            Level::Debug,
            true,
            0666
        ));

        return $logger;
    }

    private static function shouldWriteToStdout(string $channel): bool
    {
        return in_array($channel, self::STDOUT_CHANNELS, true);
    }

    private static function logDirectory(): string
    {
        $runtimePath = Yii::$app !== null ? Yii::$app->runtimePath : dirname(__DIR__, 3) . '/runtime';
        $directory = rtrim($runtimePath, '/') . '/logs';

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Log directory cannot be created: %s', $directory));
        }

        return $directory;
    }

    private static function fileName(string $channel): string
    {
        $fileName = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $channel);
        $fileName = $fileName !== null ? trim($fileName, '_') : '';

        return $fileName !== '' ? strtolower($fileName) : 'application';
    }
}
