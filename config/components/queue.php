<?php

declare(strict_types=1);

return [
    'class' => yii\queue\amqp_interop\Queue::class,
    'host' => $_ENV['RABBITMQ_HOST'] ?? 'rabbitmq',
    'port' => (int) ($_ENV['RABBITMQ_PORT'] ?? 5672),
    'user' => $_ENV['RABBITMQ_USER'] ?? 'guest',
    'password' => $_ENV['RABBITMQ_PASSWORD'] ?? 'guest',
    'vhost' => $_ENV['RABBITMQ_VHOST'] ?? '/',
    'queueName' => 'moviegate',
    'exchangeName' => 'moviegate.exchange',
    'routingKey' => 'moviegate.default',
    'as log' => yii\queue\LogBehavior::class,
];
