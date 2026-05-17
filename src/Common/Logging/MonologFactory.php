<?php

declare(strict_types=1);

namespace App\Common\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

/**
 * Produces named Monolog channels.
 */
final class MonologFactory
{
    /**
     * @param string $channel
     */
    public static function make(string $channel): Logger
    {
        $logger = new Logger($channel);
        $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));

        return $logger;
    }
}
