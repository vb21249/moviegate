<?php

declare(strict_types=1);

namespace App\Common\Logging;

use App\Common\Exceptions\RuntimeFileException;
use InvalidArgumentException;
use Yii;

/**
 * Appends timestamped lines to files inside Yii runtime.
 */
final class RuntimeFileLogger
{
    public function info(string $relativePath, string $message): bool
    {
        return $this->appendLine($relativePath, sprintf(
            "[%s] %s\n",
            date('Y-m-d H:i:s'),
            rtrim($message, "\r\n")
        ));
    }

    private function appendLine(string $relativePath, string $line): bool
    {
        $file = $this->runtimeFile($relativePath);
        $directory = dirname($file);

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            return false;
        }

        return @file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false;
    }

    private function runtimeFile(string $relativePath): string
    {
        $relativePath = $this->normalizeRelativePath($relativePath);

        if (Yii::$app === null) {
            throw new RuntimeFileException('Yii application is not initialized.');
        }

        return rtrim(Yii::$app->runtimePath, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }

    private function normalizeRelativePath(string $relativePath): string
    {
        $relativePath = str_replace('\\', '/', trim($relativePath));

        if (
            $relativePath === ''
            || str_starts_with($relativePath, '/')
            || preg_match('#^[A-Za-z]:/#', $relativePath)
            || preg_match('#(^|/)\.\.(/|$)#', $relativePath)
        ) {
            throw new InvalidArgumentException('Runtime file path must be relative and stay inside runtime.');
        }

        return trim($relativePath, '/');
    }
}
