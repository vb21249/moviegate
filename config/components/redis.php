<?php

declare(strict_types=1);

return [
    'class' => yii\redis\Connection::class,
    'hostname' => $_ENV['REDIS_HOST'] ?? 'redis',
    'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
    'database' => (int) ($_ENV['REDIS_DATABASE'] ?? 0),
];
