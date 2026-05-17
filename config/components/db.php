<?php

declare(strict_types=1);

return [
    'class' => yii\db\Connection::class,
    'dsn' => $_ENV['DB_DSN'] ?? 'mysql:host=mysql;port=3306;dbname=moviegate',
    'username' => $_ENV['DB_USERNAME'] ?? 'moviegate',
    'password' => $_ENV['DB_PASSWORD'] ?? 'moviegate',
    'charset' => 'utf8mb4',
];
