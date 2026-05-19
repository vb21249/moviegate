<?php

declare(strict_types=1);

namespace App\Modules\Feed\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Feed\Exceptions\FeedException;
use App\Modules\Feed\Interfaces\FeedRepositoryInterface;
use yii\db\Expression;
use yii\db\Query;

/**
 * Feed repository backed by feed_events and users.
 */
final class FeedRepository extends BaseRepository implements FeedRepositoryInterface
{
    private const FEED_EVENTS_TABLE = '{{%feed_events}}';
    private const USERS_TABLE = '{{%users}}';

    public function findEvents(
        int $limit,
        int $offset,
        ?int $userId = null,
        ?string $eventType = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): array {
        return $this->publicEventQuery($userId, $eventType, $entityType, $entityId)
            ->orderBy(['fe.occurred_at' => SORT_DESC, 'fe.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countEvents(
        ?int $userId = null,
        ?string $eventType = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): int {
        return (int) $this->publicEventQuery($userId, $eventType, $entityType, $entityId)
            ->count('*', $this->db());
    }

    public function findEvent(int $eventId): ?array
    {
        $event = $this->publicEventQuery()
            ->andWhere(['fe.id' => $eventId])
            ->one($this->db());

        return $event === false ? null : $event;
    }

    public function findUserEvents(
        int $userId,
        int $limit,
        int $offset,
        ?string $eventType = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): array {
        return $this->publicEventQuery($userId, $eventType, $entityType, $entityId)
            ->orderBy(['fe.occurred_at' => SORT_DESC, 'fe.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countUserEvents(
        int $userId,
        ?string $eventType = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): int {
        return (int) $this->publicEventQuery($userId, $eventType, $entityType, $entityId)
            ->count('*', $this->db());
    }

    public function appendEvent(
        int $userId,
        string $eventType,
        string $entityType,
        ?int $entityId = null,
        ?array $payload = null
    ): array {
        $now = new Expression('NOW()');
        $this->db()->createCommand()->insert(self::FEED_EVENTS_TABLE, [
            'user_id' => $userId,
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'payload_json' => $payload !== null ? json_encode($payload) : null,
            'occurred_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $eventId = (int) $this->db()->getLastInsertID();
        $event = $this->findEvent($eventId);

        if ($event === null) {
            throw new FeedException(
                'Feed event was created but could not be loaded',
                500,
                FeedException::CODE_FEED_EVENT_CREATE_FAILED
            );
        }

        return $event;
    }

    private function publicEventQuery(
        ?int $userId = null,
        ?string $eventType = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): Query {
        return $this->eventQuery()
            ->andWhere(['u.status' => 'active'])
            ->andWhere(['u.deleted_at' => null])
            ->andFilterWhere(['fe.user_id' => $userId])
            ->andFilterWhere(['fe.event_type' => $eventType])
            ->andFilterWhere(['fe.entity_type' => $entityType])
            ->andFilterWhere(['fe.entity_id' => $entityId]);
    }

    private function eventQuery(): Query
    {
        return (new Query())
            ->select([
                'id' => 'fe.id',
                'user_id' => 'fe.user_id',
                'event_type' => 'fe.event_type',
                'entity_type' => 'fe.entity_type',
                'entity_id' => 'fe.entity_id',
                'payload_json' => 'fe.payload_json',
                'occurred_at' => 'fe.occurred_at',
                'created_at' => 'fe.created_at',
                'updated_at' => 'fe.updated_at',
                'username' => 'u.username',
                'user_avatar_url' => 'u.avatar_url',
            ])
            ->from(['fe' => self::FEED_EVENTS_TABLE])
            ->innerJoin(['u' => self::USERS_TABLE], 'u.id = fe.user_id');
    }
}
