<?php

declare(strict_types=1);

namespace App\Commands;

use App\Modules\Common\Interfaces\CommonRepositoryInterface;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Log retention maintenance commands.
 */
final class LogController extends Controller
{
    public int|string|null $apiDays = null;
    public int|string|null $integrationDays = null;
    public bool|int|string $dryRun = false;

    /**
     * @return list<string>
     */
    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'apiDays',
            'integrationDays',
            'dryRun',
        ]);
    }

    public function actionCleanup(): int
    {
        $repository = Yii::createObject(CommonRepositoryInterface::class);
        $apiDays = $this->retentionDays($this->apiDays, 'apiDays', 30);
        $integrationDays = $this->retentionDays($this->integrationDays, 'integrationDays', 90);
        $apiThreshold = $this->threshold($apiDays);
        $integrationThreshold = $this->threshold($integrationDays);
        $dryRun = $this->asBool($this->dryRun);

        $apiCount = $dryRun
            ? $repository->countApiLogsOlderThan($apiThreshold)
            : $repository->deleteApiLogsOlderThan($apiThreshold);
        $integrationCount = $dryRun
            ? $repository->countIntegrationLogsOlderThan($integrationThreshold)
            : $repository->deleteIntegrationLogsOlderThan($integrationThreshold);

        $this->stdout(sprintf(
            "%s api_logs older than %d days (%s): %d\n",
            $dryRun ? 'Would delete' : 'Deleted',
            $apiDays,
            $apiThreshold,
            $apiCount
        ));
        $this->stdout(sprintf(
            "%s integration_logs older than %d days (%s): %d\n",
            $dryRun ? 'Would delete' : 'Deleted',
            $integrationDays,
            $integrationThreshold,
            $integrationCount
        ));

        return ExitCode::OK;
    }

    private function retentionDays(int|string|null $value, string $key, int $default): int
    {
        $configured = $value ?? (Yii::$app->params['logRetention'][$key] ?? $default);

        return max(1, (int) $configured);
    }

    private function threshold(int $days): string
    {
        return date('Y-m-d H:i:s', time() - ($days * 86400));
    }

    private function asBool(bool|int|string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
