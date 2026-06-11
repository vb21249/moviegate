<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Logging\RuntimeFileLogger;
use App\Common\Services\FileLockService;
use FilesystemIterator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Yii;
use yii\console\Application;

final class RuntimeFileServicesTest extends TestCase
{
    private string $runtimePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runtimePath = sys_get_temp_dir() . '/moviegate-runtime-services-' . bin2hex(random_bytes(6));
        mkdir($this->runtimePath, 0775, true);

        new Application([
            'id' => 'runtime-file-services-test',
            'basePath' => dirname(__DIR__, 2),
            'runtimePath' => $this->runtimePath,
        ]);
    }

    protected function tearDown(): void
    {
        if (Yii::$app !== null && Yii::$app->has('errorHandler')) {
            Yii::$app->getErrorHandler()->unregister();
        }

        Yii::$app = null;
        $this->removeDirectory($this->runtimePath);

        parent::tearDown();
    }

    public function testRuntimeFileLoggerAppendsTimestampedLines(): void
    {
        $logger = new RuntimeFileLogger();

        self::assertTrue($logger->info('logs/integration/tmdb/commands/import-catalog-2026-05-21.log', 'first message'));
        self::assertTrue($logger->info('logs/integration/tmdb/commands/import-catalog-2026-05-21.log', 'second message'));

        $file = $this->runtimePath . '/logs/integration/tmdb/commands/import-catalog-2026-05-21.log';
        self::assertFileExists($file);

        $lines = file($file, FILE_IGNORE_NEW_LINES);

        self::assertIsArray($lines);
        self::assertCount(2, $lines);
        self::assertMatchesRegularExpression('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] first message$/', $lines[0]);
        self::assertMatchesRegularExpression('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] second message$/', $lines[1]);
    }

    public function testFileLockServiceCreatesLockAndReturnsCallbackResult(): void
    {
        $called = false;

        $result = (new FileLockService())->run('locks/tmdb-api.lock', static function () use (&$called): string {
            $called = true;

            return 'done';
        });

        self::assertSame('done', $result);
        self::assertTrue($called);
        self::assertFileExists($this->runtimePath . '/locks/tmdb-api.lock');
    }

    public function testRuntimeFileLoggerRejectsAbsolutePath(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new RuntimeFileLogger())->info('/tmp/command.log', 'message');
    }

    public function testFileLockServiceRejectsPathTraversal(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new FileLockService())->run('../tmdb-api.lock', static fn (): string => 'done');
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($fileInfo->getPathname());
            } else {
                unlink($fileInfo->getPathname());
            }
        }

        rmdir($path);
    }
}
