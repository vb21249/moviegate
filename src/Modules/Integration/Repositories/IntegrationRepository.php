<?php

declare(strict_types=1);

namespace App\Modules\Integration\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Integration\Interfaces\IntegrationRepositoryInterface;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Json;

/**
 * Integration repository for sync audit logs.
 */
final class IntegrationRepository extends BaseRepository implements IntegrationRepositoryInterface
{
    private const INTEGRATION_LOGS_TABLE = '{{%integration_logs}}';

    public function query(array $criteria = []): array
    {
        $criteria = isset($criteria['query']) && is_array($criteria['query']) ? $criteria['query'] : $criteria;
        $limit = min(max((int) ($criteria['limit'] ?? 20), 1), 100);

        $query = (new Query())
            ->select([
                'id',
                'correlation_id',
                'service',
                'operation',
                'request_payload',
                'response_payload',
                'status',
                'created_at',
            ])
            ->from(self::INTEGRATION_LOGS_TABLE)
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
            ->limit($limit);

        $service = isset($criteria['service']) ? trim((string) $criteria['service']) : '';

        if ($service !== '') {
            $query->andWhere(['service' => $service]);
        }

        return array_map(
            fn (array $row): array => $this->decodeLogPayloads($row),
            $query->all($this->db())
        );
    }

    public function logIntegration(
        string $correlationId,
        string $service,
        string $operation,
        array $requestPayload,
        array $responsePayload,
        string $status
    ): int {
        $this->db()->createCommand()->insert(self::INTEGRATION_LOGS_TABLE, [
            'correlation_id' => $correlationId,
            'service' => $service,
            'operation' => $operation,
            'request_payload' => $requestPayload !== [] ? Json::encode($requestPayload) : null,
            'response_payload' => $responsePayload !== [] ? Json::encode($responsePayload) : null,
            'status' => $status,
            'created_at' => new Expression('NOW()'),
        ])->execute();

        return (int) $this->db()->getLastInsertID();
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function decodeLogPayloads(array $row): array
    {
        $row['request_payload'] = $this->decodePayload($row['request_payload'] ?? null);
        $row['response_payload'] = $this->decodePayload($row['response_payload'] ?? null);

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
}
