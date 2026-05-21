<?php

declare(strict_types=1);

$runtimePath = trim((string) ($_ENV['APP_RUNTIME_PATH'] ?? $_SERVER['APP_RUNTIME_PATH'] ?? ''));

return $runtimePath !== '' ? rtrim($runtimePath, '/') : dirname(__DIR__) . '/runtime';
