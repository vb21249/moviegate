<?php

declare(strict_types=1);

namespace App\Common\Logging;

use App\Common\Contracts\ApiLogWriterInterface;
use Yii;
use yii\db\Connection;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Writes API traffic logs to the api_logs table.
 */
final class DbApiLogWriter implements ApiLogWriterInterface
{
    private const TABLE = '{{%api_logs}}';

    public function write(
        string $correlationId,
        string $requestMethod,
        string $requestUri,
        mixed $requestBody,
        int $responseStatus,
        mixed $responseBody
    ): void {
        if (Yii::$app === null || !Yii::$app->has('db')) {
            return;
        }

        $db = Yii::$app->get('db', false);

        if (!$db instanceof Connection) {
            return;
        }

        $db->createCommand()->insert(self::TABLE, [
            'correlation_id' => $this->limit($correlationId, 64),
            'request_method' => $this->limit($requestMethod, 16),
            'request_uri' => $this->limit($requestUri, 255),
            'request_body' => $this->encodePayload($requestBody),
            'response_status' => $responseStatus,
            'response_body' => $this->encodePayload($responseBody),
            'created_at' => new Expression('NOW()'),
        ])->execute();
    }

    private function encodePayload(mixed $payload): ?string
    {
        if ($payload === null || $payload === [] || $payload === '') {
            return null;
        }

        return Json::encode($payload);
    }

    private function limit(string $value, int $length): string
    {
        return mb_substr($value, 0, $length);
    }
}
