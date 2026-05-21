<?php

declare(strict_types=1);

use App\Common\Bootstrap\ApplicationBootstrap;

$params = require __DIR__ . '/params.php';
$modules = require __DIR__ . '/modules.php';
$runtimePath = require __DIR__ . '/runtime.php';

return [
    'id' => 'moviegate-console',
    'basePath' => dirname(__DIR__),
    'runtimePath' => $runtimePath,
    'bootstrap' => [
        'queue',
        ApplicationBootstrap::class,
    ],
    'controllerNamespace' => 'App\\Commands',
    'controllerMap' => [
        'migrate' => [
            'class' => yii\console\controllers\MigrateController::class,
            'migrationPath' => '@app/migrations',
            'db' => 'db',
        ],
    ],
    'components' => [
        'db' => require __DIR__ . '/components/db.php',
        'redis' => require __DIR__ . '/components/redis.php',
        'cache' => require __DIR__ . '/components/cache.php',
        'queue' => require __DIR__ . '/components/queue.php',
        'log' => require __DIR__ . '/components/log.php',
        'authManager' => [
            'class' => yii\rbac\DbManager::class,
        ],
    ],
    'params' => $params,
    'modules' => array_map(
        static fn (string $className): array => ['class' => $className],
        $modules
    ),
];
