<?php

declare(strict_types=1);

namespace App\Commands;

use App\Modules\Integration\Clients\TmdbClientInterface;
use App\Modules\Integration\Exceptions\IntegrationException;
use App\Modules\Integration\Interfaces\IntegrationServiceInterface;
use App\Modules\Integration\Requests\IntegrationRequest;
use Throwable;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;

/**
 * TMDB catalog import commands.
 */
final class TmdbController extends Controller
{
    private const MIN_TMDB_REQUEST_INTERVAL_MS = 300;
    private const TMDB_LOCK_FILE = 'locks/tmdb-api.lock';
    private const CATALOG_CURSOR_FILE = 'tmdb-import-catalog-cursor.json';
    private const MOVIES_TABLE = '{{%movies}}';

    public int|string $pages = 1;
    public int|string $limit = 100;
    public int|string $maxPages = 500;
    public int|string|null $startPage = null;
    public string $language = 'en-US';
    public ?string $region = null;
    public bool|int|string $resetCursor = false;
    public int|string $sleepMs = self::MIN_TMDB_REQUEST_INTERVAL_MS;
    private ?float $lastTmdbRequestAt = null;

    /**
     * @return list<string>
     */
    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'pages',
            'limit',
            'maxPages',
            'startPage',
            'language',
            'region',
            'resetCursor',
            'sleepMs',
        ]);
    }

    public function actionImportPopular(): int
    {
        return $this->withLock($this->getTmdbLockFilePath(), 'TMDB import', fn (): int => $this->importPopular());
    }

    public function actionImportCatalog(): int
    {
        return $this->withLock($this->getTmdbLockFilePath(), 'TMDB catalog import', function (): int {
            if (!$this->tmdbCredentialsConfigured()) {
                $this->logError('TMDB credentials are not configured; import skipped.');

                return ExitCode::OK;
            }

            $pages = max(1, min((int) $this->pages, 50));
            $maxPages = max(1, min((int) $this->maxPages, 500));
            $sleepMs = max(self::MIN_TMDB_REQUEST_INTERVAL_MS, (int) $this->sleepMs);
            $page = $this->initialCatalogPage($maxPages);
            $tmdbClient = Yii::createObject(TmdbClientInterface::class);
            $integrationService = Yii::createObject(IntegrationServiceInterface::class);
            $seenTmdbIds = [];
            $synced = 0;
            $failed = 0;

            for ($processedPages = 0; $processedPages < $pages; ++$processedPages) {
                try {
                    $this->throttleTmdbRequest($sleepMs);
                    $response = $tmdbClient->get('discover/movie', $this->discoverQuery($page));
                } catch (Throwable $exception) {
                    $this->logError(sprintf("Discover page %d failed: %s", $page, $exception->getMessage()));
                    ++$failed;
                    $page = $this->nextCatalogPage($page, $maxPages);

                    continue;
                }

                $results = isset($response['results']) && is_array($response['results']) ? $response['results'] : [];
                $totalPages = isset($response['total_pages']) ? max(1, (int) $response['total_pages']) : $maxPages;
                $effectiveMaxPage = min($maxPages, $totalPages);
                $this->logInfo(sprintf("Importing TMDB discover page %d: %d movies", $page, count($results)));

                foreach ($results as $result) {
                    if (!is_array($result) || empty($result['id'])) {
                        continue;
                    }

                    $tmdbId = (int) $result['id'];

                    if (isset($seenTmdbIds[$tmdbId])) {
                        continue;
                    }

                    $seenTmdbIds[$tmdbId] = true;

                    if ($this->syncMovie($integrationService, $tmdbId, $sleepMs)) {
                        ++$synced;
                    } else {
                        ++$failed;
                    }
                }

                $page = $this->nextCatalogPage($page, $effectiveMaxPage);
                $this->saveCatalogCursor($page);
            }

            $this->logInfo(sprintf(
                'TMDB catalog import finished: %d synced, %d failed, next page %d',
                $synced,
                $failed,
                $page
            ));

            return $failed > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
        });
    }

    public function actionRefreshImported(): int
    {
        return $this->withLock($this->getTmdbLockFilePath(), 'TMDB imported refresh', function (): int {
            if (!$this->tmdbCredentialsConfigured()) {
                $this->logError('TMDB credentials are not configured; refresh skipped.');

                return ExitCode::OK;
            }

            $limit = max(1, min((int) $this->limit, 500));
            $sleepMs = max(self::MIN_TMDB_REQUEST_INTERVAL_MS, (int) $this->sleepMs);
            $integrationService = Yii::createObject(IntegrationServiceInterface::class);
            $tmdbIds = $this->importedTmdbIds($limit);
            $synced = 0;
            $failed = 0;

            $this->logInfo(sprintf('Refreshing %d imported TMDB movies', count($tmdbIds)));

            foreach ($tmdbIds as $tmdbId) {
                if ($this->syncMovie($integrationService, $tmdbId, $sleepMs)) {
                    ++$synced;
                } else {
                    ++$failed;
                }
            }

            $this->logInfo(sprintf(
                'TMDB imported refresh finished: %d synced, %d failed',
                $synced,
                $failed
            ));

            return $failed > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
        });
    }

    private function importPopular(): int
    {
        if (!$this->tmdbCredentialsConfigured()) {
            $this->logError('TMDB credentials are not configured; import skipped.');

            return ExitCode::OK;
        }

        $pages = max(1, min((int) $this->pages, 50));
        $sleepMs = max(self::MIN_TMDB_REQUEST_INTERVAL_MS, (int) $this->sleepMs);
        $tmdbClient = Yii::createObject(TmdbClientInterface::class);
        $integrationService = Yii::createObject(IntegrationServiceInterface::class);
        $seenTmdbIds = [];
        $synced = 0;
        $failed = 0;

        for ($page = 1; $page <= $pages; ++$page) {
            try {
                $this->throttleTmdbRequest($sleepMs);
                $response = $tmdbClient->get('movie/popular', $this->popularQuery($page));
            } catch (Throwable $exception) {
                $this->logError(sprintf("Page %d failed: %s", $page, $exception->getMessage()));
                ++$failed;

                continue;
            }

            $results = isset($response['results']) && is_array($response['results']) ? $response['results'] : [];
            $this->logInfo(sprintf("Importing TMDB popular page %d: %d movies", $page, count($results)));

            foreach ($results as $result) {
                if (!is_array($result) || empty($result['id'])) {
                    continue;
                }

                $tmdbId = (int) $result['id'];

                if (isset($seenTmdbIds[$tmdbId])) {
                    continue;
                }

                $seenTmdbIds[$tmdbId] = true;

                if ($this->syncMovie($integrationService, $tmdbId, $sleepMs)) {
                    ++$synced;
                } else {
                    ++$failed;
                }
            }
        }

        $this->logInfo(sprintf(
            'TMDB import finished: %d synced, %d failed, %d unique ids',
            $synced,
            $failed,
            count($seenTmdbIds)
        ));

        return $failed > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    /**
     * @param int $page
     *
     * @return array<string, mixed>
     */
    private function popularQuery(int $page): array
    {
        $query = [
            'page' => $page,
            'language' => $this->language,
        ];

        if ($this->region !== null && trim($this->region) !== '') {
            $query['region'] = trim($this->region);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function discoverQuery(int $page): array
    {
        $query = [
            'page' => $page,
            'language' => $this->language,
            'sort_by' => 'popularity.desc',
            'include_adult' => 'false',
            'include_video' => 'false',
        ];

        if ($this->region !== null && trim($this->region) !== '') {
            $query['region'] = trim($this->region);
        }

        return $query;
    }

    private function syncMovie(IntegrationServiceInterface $integrationService, int $tmdbId, int $sleepMs): bool
    {
        $request = new IntegrationRequest();
        $request->tmdb_id = $tmdbId;
        $request->language = $this->language;

        if (!$request->validate()) {
            $firstErrors = $request->getFirstErrors();
            $this->logError(sprintf(
                'TMDB %d skipped: %s',
                $tmdbId,
                $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors)
            ));

            return false;
        }

        try {
            $this->throttleTmdbRequest($sleepMs);
            $response = $integrationService->syncTmdb($request)->toArray();
            $movie = $response['movie'] ?? [];
            $title = is_array($movie) ? (string) ($movie['title'] ?? 'unknown') : 'unknown';
            $this->logInfo(sprintf('TMDB %d synced: %s', $tmdbId, $title));

            return true;
        } catch (IntegrationException $exception) {
            $this->logError(sprintf(
                'TMDB %d failed [%s]: %s',
                $tmdbId,
                $exception->getErrorCode(),
                $exception->getMessage()
            ));

            return false;
        } catch (Throwable $exception) {
            $this->logError(sprintf("TMDB %d failed: %s", $tmdbId, $exception->getMessage()));

            return false;
        }
    }

    private function throttleTmdbRequest(int $sleepMs): void
    {
        if ($this->lastTmdbRequestAt !== null) {
            $elapsedUs = (int) ((microtime(true) - $this->lastTmdbRequestAt) * 1_000_000);
            $waitUs = ($sleepMs * 1000) - $elapsedUs;

            if ($waitUs > 0) {
                usleep($waitUs);
            }
        }

        $this->lastTmdbRequestAt = microtime(true);
    }

    /**
     * @return list<int>
     */
    private function importedTmdbIds(int $limit): array
    {
        return array_map('intval', (new Query())
            ->select('tmdb_id')
            ->from(self::MOVIES_TABLE)
            ->andWhere(['not', ['tmdb_id' => null]])
            ->andWhere(['deleted_at' => null])
            ->orderBy(['updated_at' => SORT_ASC, 'id' => SORT_ASC])
            ->limit($limit)
            ->column(Yii::$app->db));
    }

    private function initialCatalogPage(int $maxPages): int
    {
        if ($this->startPage !== null && $this->startPage !== '') {
            return max(1, min((int) $this->startPage, $maxPages));
        }

        if (filter_var($this->resetCursor, FILTER_VALIDATE_BOOL)) {
            $this->saveCatalogCursor(1);

            return 1;
        }

        return max(1, min($this->readCatalogCursor(), $maxPages));
    }

    private function nextCatalogPage(int $currentPage, int $maxPages): int
    {
        return $currentPage >= $maxPages ? 1 : $currentPage + 1;
    }

    private function readCatalogCursor(): int
    {
        $file = $this->catalogCursorFile();

        if (!is_file($file)) {
            return 1;
        }

        $payload = json_decode((string) file_get_contents($file), true);

        return is_array($payload) && isset($payload['next_page']) ? (int) $payload['next_page'] : 1;
    }

    private function saveCatalogCursor(int $nextPage): void
    {
        $file = $this->catalogCursorFile();
        $directory = dirname($file);

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($file, json_encode([
            'next_page' => $nextPage,
            'updated_at' => date('Y-m-d H:i:s'),
        ], JSON_THROW_ON_ERROR));
    }

    private function catalogCursorFile(): string
    {
        return rtrim(Yii::$app->runtimePath, '/') . '/' . self::CATALOG_CURSOR_FILE;
    }

    /**
     * @param callable():int $callback
     */
    private function withLock(string $lockFile, string $label, callable $callback): int
    {
        $directory = dirname($lockFile);

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            $this->logError(sprintf('%s lock directory cannot be created: %s', $label, $directory));

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $lock = $this->openLockFile($lockFile);

        if ($lock === false) {
            $this->logError(sprintf('%s lock file cannot be opened: %s', $label, $lockFile));

            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            $this->logInfo(sprintf('%s is already running; skipped.', $label));

            return ExitCode::OK;
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

    private function logInfo(string $message): void
    {
        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);

        $this->appendCommandLog($line);
        $this->stdout($line);
    }

    private function logError(string $message): void
    {
        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);

        $this->appendCommandLog($line);
        $this->stderr($line);
    }

    private function appendCommandLog(string $line): void
    {
        $file = $this->commandLogFile();
        $directory = dirname($file);

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            $this->stderr(sprintf(
                "[%s] TMDB command log directory cannot be created: %s\n",
                date('Y-m-d H:i:s'),
                $directory
            ));

            return;
        }

        if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
            $this->stderr(sprintf(
                "[%s] TMDB command log file cannot be written: %s\n",
                date('Y-m-d H:i:s'),
                $file
            ));
        }
    }

    private function commandLogFile(): string
    {
        $actionId = $this->action?->id ?? 'command';
        $fileName = preg_replace('/[^A-Za-z0-9_.-]+/', '-', $actionId);
        $fileName = $fileName !== null ? trim($fileName, '-') : '';

        return rtrim(Yii::$app->runtimePath, '/') . '/logs/integration/tmdb/commands/' . ($fileName !== '' ? $fileName : 'command') . '.log';
    }

    private function tmdbCredentialsConfigured(): bool
    {
        $apiKey = trim((string) (Yii::$app->params['tmdb']['apiKey'] ?? ''));
        $accessToken = trim((string) (Yii::$app->params['tmdb']['accessToken'] ?? ''));

        return $this->isRealCredential($apiKey) || $this->isRealCredential($accessToken);
    }

    private function isRealCredential(string $credential): bool
    {
        return $credential !== '' && $credential !== 'change-me';
    }

    private function getTmdbLockFilePath(): string
    {
        return rtrim(Yii::$app->runtimePath, '/') . '/' . self::TMDB_LOCK_FILE;
    }

}
