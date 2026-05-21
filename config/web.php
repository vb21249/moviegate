<?php

declare(strict_types=1);

use App\Common\Bootstrap\ApplicationBootstrap;
use App\Common\Components\ApiErrorHandler;
use App\Common\Components\JsonResponseFormatter;
use yii\web\JsonParser;

$params = require __DIR__ . '/params.php';
$modules = require __DIR__ . '/modules.php';
$runtimePath = require __DIR__ . '/runtime.php';

return [
    'id' => 'moviegate-web',
    'basePath' => dirname(__DIR__),
    'runtimePath' => $runtimePath,
    'bootstrap' => [
        ApplicationBootstrap::class,
    ],
    'controllerNamespace' => 'App\\Modules\\Common\\Controllers',
    'components' => [
        'db' => require __DIR__ . '/components/db.php',
        'redis' => require __DIR__ . '/components/redis.php',
        'cache' => require __DIR__ . '/components/cache.php',
        'queue' => require __DIR__ . '/components/queue.php',
        'log' => require __DIR__ . '/components/log.php',
        'request' => [
            'cookieValidationKey' => $_ENV['APP_KEY'] ?? 'change-me',
            'parsers' => [
                'application/json' => JsonParser::class,
            ],
        ],
        'user' => [
            'class' => yii\web\User::class,
            'identityClass' => App\Modules\User\Models\UserRecord::class,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'formatters' => [
                yii\web\Response::FORMAT_JSON => [
                    'class' => JsonResponseFormatter::class,
                    'prettyPrint' => YII_DEBUG,
                ],
            ],
        ],
        'errorHandler' => [
            'class' => ApiErrorHandler::class,
            'errorAction' => null,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => require __DIR__ . '/routes.php',
        ],
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
