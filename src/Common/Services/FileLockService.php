<?php

declare(strict_types=1);

namespace App\Common\Services;

use App\Common\Exceptions\FileLockUnavailableException;
use App\Common\Exceptions\RuntimeFileException;
use InvalidArgumentException;
use Yii;

/**
 * Runs callbacks under a runtime file lock.
 */
final class FileLockService
{
    /**
     * @template T
     *
     * @param string $relativePath
     * @param callable():T $callback
     *
     * @return T
     */
    public function run(string $relativePath, callable $callback): mixed
    {
        $lockFile = $this->runtimeFile($relativePath);
        $directory = dirname($lockFile);

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeFileException(sprintf('Lock directory cannot be created: %s', $directory));
        }

        $lock = $this->openLockFile($lockFile);

        if ($lock === false) {
            throw new RuntimeFileException(sprintf('Lock file cannot be opened: %s', $lockFile));
        }

        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);

            throw new FileLockUnavailableException(sprintf('Lock is already held: %s', $relativePath));
        }

        try {
            return $callback();
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * @return resource|false
     */
    private function openLockFile(string $lockFile): mixed
    {
        if (is_file($lockFile) && !is_writable($lockFile) && is_readable($lockFile)) {
            return @fopen($lockFile, 'r');
        }

        $lock = @fopen($lockFile, 'c');

        if ($lock !== false) {
            @chmod($lockFile, 0666);
        }

        return $lock;
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
