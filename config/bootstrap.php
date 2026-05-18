<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

\Yii::setAlias('@App', dirname(__DIR__) . '/src');

if (file_exists(dirname(__DIR__) . '/.env')) {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

defined('YII_DEBUG') || define('YII_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL));
defined('YII_ENV') || define('YII_ENV', $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'prod');

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

require __DIR__ . '/container.php';
