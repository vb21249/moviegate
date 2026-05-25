<?php

declare(strict_types=1);

namespace App\Modules\Common\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Common\Interfaces\CommonRepositoryInterface;
use yii\db\Expression;
use yii\db\Query;

/**
 * Common repository for shared infrastructure read models.
 */
final class CommonRepository extends BaseRepository implements CommonRepositoryInterface
{
    private const API_LOGS_TABLE = '{{%api_logs}}';
    private const INTEGRATION_LOGS_TABLE = '{{%integration_logs}}';

    public function query(array $criteria = []): array
    {
        return [];
    }

    public function findApiLogs(
        int $limit,
        int $offset,
        ?string $correlationId = null,
        ?string $requestMethod = null,
        ?int $responseStatus = null,
        ?string $createdFrom = null,
        ?string $createdTo = null
    ): array {
        return array_map(
            fn (array $row): array => $this->decodeApiLog($row),
            $this->apiLogQuery($correlationId, $requestMethod, $responseStatus, $createdFrom, $createdTo)
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit($limit)
                ->offset($offset)
                ->all($this->db())
        );
    }

    public function countApiLogs(
        ?string $correlationId = null,
        ?string $requestMethod = null,
        ?int $responseStatus = null,
        ?string $createdFrom = null,
        ?string $createdTo = null
    ): int {
        return (int) $this->apiLogQuery($correlationId, $requestMethod, $responseStatus, $createdFrom, $createdTo)
            ->count('*', $this->db());
    }

    public function findApiLog(int $id): ?array
    {
        $row = $this->apiLogQuery()
            ->andWhere(['id' => $id])
            ->one($this->db());

        return $row === false ? null : $this->decodeApiLog($row);
    }

    public function apiLogSummary(?string $createdFrom = null, ?string $createdTo = null): array
    {
        $query = $this->apiLogQuery(createdFrom: $createdFrom, createdTo: $createdTo);
        $statusRows = (clone $query)
            ->select([
                'response_status',
                'count' => new Expression('COUNT(*)'),
            ])
            ->groupBy('response_status')
            ->orderBy(['response_status' => SORT_ASC])
            ->all($this->db());
        $byStatus = [];
        $byFamily = [
            '1xx' => 0,
            '2xx' => 0,
            '3xx' => 0,
            '4xx' => 0,
            '5xx' => 0,
        ];

        foreach ($statusRows as $row) {
            $status = (int) $row['response_status'];
            $count = (int) $row['count'];
            $byStatus[(string) $status] = $count;

            $family = intdiv($status, 100) . 'xx';

            if (array_key_exists($family, $byFamily)) {
                $byFamily[$family] += $count;
            }
        }

        $latest = (clone $query)
            ->select(['created_at'])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(1)
            ->scalar($this->db());

        return [
            'total' => array_sum($byStatus),
            'by_status' => $byStatus,
            'by_family' => $byFamily,
            'latest_created_at' => $latest !== false ? $latest : null,
        ];
    }

    public function countApiLogsOlderThan(string $threshold): int
    {
        return $this->countOlderThan(self::API_LOGS_TABLE, $threshold);
    }

    public function deleteApiLogsOlderThan(string $threshold): int
    {
        return $this->deleteOlderThan(self::API_LOGS_TABLE, $threshold);
    }

    public function countIntegrationLogsOlderThan(string $threshold): int
    {
        return $this->countOlderThan(self::INTEGRATION_LOGS_TABLE, $threshold);
    }

    public function deleteIntegrationLogsOlderThan(string $threshold): int
    {
        return $this->deleteOlderThan(self::INTEGRATION_LOGS_TABLE, $threshold);
    }

    private function apiLogQuery(
        ?string $correlationId = null,
        ?string $requestMethod = null,
        ?int $responseStatus = null,
        ?string $createdFrom = null,
        ?string $createdTo = null
    ): Query {
        $query = (new Query())
            ->select([
                'id',
                'correlation_id',
                'request_method',
                'request_uri',
                'request_body',
                'response_status',
                'response_body',
                'created_at',
            ])
            ->from(self::API_LOGS_TABLE)
            ->andFilterWhere([
                'correlation_id' => $correlationId,
                'request_method' => $requestMethod,
                'response_status' => $responseStatus,
            ]);

        if ($createdFrom !== null) {
            $query->andWhere(['>=', 'created_at', $createdFrom]);
        }

        if ($createdTo !== null) {
            $query->andWhere(['<=', 'created_at', $createdTo]);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function decodeApiLog(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['response_status'] = (int) $row['response_status'];
        $row['request_body'] = $this->decodePayload($row['request_body'] ?? null);
        $row['response_body'] = $this->decodePayload($row['response_body'] ?? null);

        return $row;
    }

    private function decodePayload(mixed $payload): mixed
    {
        if (!is_string($payload) || $payload === '') {
            return $payload;
        }

        $decoded = json_decode($payload, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $payload;
    }

    private function countOlderThan(string $table, string $threshold): int
    {
        return (int) (new Query())
            ->from($table)
            ->andWhere(['<', 'created_at', $threshold])
            ->count('*', $this->db());
    }

    private function deleteOlderThan(string $table, string $threshold): int
    {
        return $this->db()->createCommand()
            ->delete($table, ['<', 'created_at', $threshold])
            ->execute();
    }
}
