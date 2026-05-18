<?php

declare(strict_types=1);

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => App\Common\Logging\MonologTarget::class,
            'levels' => ['error', 'warning'],
            'categories' => ['application', 'yii\\*'],
        ],
        [
            'class' => yii\log\FileTarget::class,
            'levels' => ['error', 'warning'],
            'categories' => ['application', 'yii\\*'],
            'logFile' => dirname(__DIR__, 2) . '/runtime/logs/app.log',
        ],
    ],
];
