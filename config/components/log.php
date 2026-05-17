<?php

declare(strict_types=1);

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => App\Common\Logging\MonologTarget::class,
            'levels' => ['error', 'warning', 'info'],
            'categories' => ['application', 'yii\\*'],
        ],
    ],
];
