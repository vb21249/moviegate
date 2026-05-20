<?php

declare(strict_types=1);

namespace App\Modules\Notification\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Notification\Exceptions\NotificationException;
use App\Modules\Notification\Interfaces\NotificationRepositoryInterface;
use yii\db\Expression;
use yii\db\Query;

/**
 * Notification repository backed by notifications table.
 */
final class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    private const NOTIFICATIONS_TABLE = '{{%notifications}}';

    public function findUserNotifications(
        int $userId,
        int $limit,
        int $offset,
        ?string $type = null,
        bool $unreadOnly = false
    ): array {
        return $this->userNotificationQuery($userId, $type, $unreadOnly)
            ->orderBy(['n.created_at' => SORT_DESC, 'n.id' => SORT_DESC])
            ->limit($limit)
            ->offset($offset)
            ->all($this->db());
    }

    public function countUserNotifications(int $userId, ?string $type = null, bool $unreadOnly = false): int
    {
        return (int) $this->userNotificationQuery($userId, $type, $unreadOnly)
            ->count('*', $this->db());
    }

    public function countUnread(int $userId): int
    {
        return (int) (new Query())
            ->from(self::NOTIFICATIONS_TABLE)
            ->andWhere([
                'user_id' => $userId,
                'read_at' => null,
            ])
            ->count('*', $this->db());
    }

    public function findUserNotification(int $notificationId, int $userId): ?array
    {
        $notification = $this->notificationQuery()
            ->andWhere([
                'n.id' => $notificationId,
                'n.user_id' => $userId,
            ])
            ->one($this->db());

        return $notification === false ? null : $notification;
    }

    public function createNotification(int $userId, string $type, array $payload): array
    {
        $now = new Expression('NOW()');
        $this->db()->createCommand()->insert(self::NOTIFICATIONS_TABLE, [
            'user_id' => $userId,
            'type' => $type,
            'payload_json' => json_encode($payload),
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $notificationId = (int) $this->db()->getLastInsertID();
        $notification = $this->findUserNotification($notificationId, $userId);

        if ($notification === null) {
            throw new NotificationException(
                'Notification was created but could not be loaded',
                500,
                NotificationException::CODE_NOTIFICATION_CREATE_FAILED
            );
        }

        return $notification;
    }

    public function markRead(int $notificationId, int $userId): void
    {
        $now = new Expression('NOW()');
        $this->db()->createCommand()
            ->update(self::NOTIFICATIONS_TABLE, [
                'read_at' => $now,
                'updated_at' => $now,
            ], [
                'id' => $notificationId,
                'user_id' => $userId,
                'read_at' => null,
            ])
            ->execute();
    }

    public function markAllRead(int $userId, ?string $type = null): int
    {
        $where = [
            'user_id' => $userId,
            'read_at' => null,
        ];

        if ($type !== null) {
            $where['type'] = $type;
        }

        $now = new Expression('NOW()');

        return $this->db()->createCommand()
            ->update(self::NOTIFICATIONS_TABLE, [
                'read_at' => $now,
                'updated_at' => $now,
            ], $where)
            ->execute();
    }

    private function userNotificationQuery(int $userId, ?string $type = null, bool $unreadOnly = false): Query
    {
        $query = $this->notificationQuery()
            ->andWhere(['n.user_id' => $userId])
            ->andFilterWhere(['n.type' => $type]);

        if ($unreadOnly) {
            $query->andWhere(['n.read_at' => null]);
        }

        return $query;
    }

    private function notificationQuery(): Query
    {
        return (new Query())
            ->select([
                'id' => 'n.id',
                'user_id' => 'n.user_id',
                'type' => 'n.type',
                'payload_json' => 'n.payload_json',
                'read_at' => 'n.read_at',
                'created_at' => 'n.created_at',
                'updated_at' => 'n.updated_at',
            ])
            ->from(['n' => self::NOTIFICATIONS_TABLE]);
    }
}
