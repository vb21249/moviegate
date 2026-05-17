<?php

declare(strict_types=1);

$definitions = require __DIR__ . '/di.php';

foreach ($definitions as $id => $definition) {
    \Yii::$container->set($id, $definition);
}
