<?php

declare(strict_types=1);

use yii\web\Application;

require dirname(__DIR__) . '/config/bootstrap.php';

$config = require dirname(__DIR__) . '/config/web.php';

(new Application($config))->run();
