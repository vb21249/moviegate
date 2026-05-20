<?php

declare(strict_types=1);

namespace App\Modules\Notification\Services;

use App\Common\Services\AbstractService;
use App\Modules\Notification\Exceptions\NotificationException;
use App\Modules\Notification\Interfaces\NotificationRepositoryInterface;
use App\Modules\Notification\Interfaces\NotificationServiceInterface;
use App\Modules\Notification\Mappers\NotificationMapper;
use App\Modules\Notification\Requests\NotificationRequest;
use App\Modules\Notification\Responses\NotificationResponse;
use App\Modules\Notification\Transformers\NotificationTransformer;

/**
 * Notification application service for user-scoped notification lifecycle.
 */
final class NotificationService extends AbstractService implements NotificationServiceInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $repository,
        private readonly NotificationMapper $mapper,
        private readonly NotificationTransformer $transformer,
    ) {
    }

    public function index(NotificationRequest $request, int $userId): NotificationResponse
    {
        $notifications = array_map(
            fn (array $notification): array => $this->mapper->mapNotification($notification)->toArray(),
            $this->repository->findUserNotifications(
                $userId,
                $request->limit(),
                $request->offset(),
                $request->type(),
                $request->unreadOnly()
            )
        );

        return new NotificationResponse([
            'items' => $notifications,
            'pagination' => $this->pagination(
                $request,
                $this->repository->countUserNotifications($userId, $request->type(), $request->unreadOnly())
            ),
            'unread_count' => $this->repository->countUnread($userId),
        ]);
    }

    public function view(int $notificationId, int $userId): NotificationResponse
    {
        return new NotificationResponse([
            'notification' => $this->mapper
                ->mapNotification($this->findNotificationOrFail($notificationId, $userId))
                ->toArray(),
            'unread_count' => $this->repository->countUnread($userId),
        ]);
    }

    public function markRead(int $notificationId, int $userId): NotificationResponse
    {
        $notification = $this->findNotificationOrFail($notificationId, $userId);
        $this->repository->markRead($notificationId, $userId);
        $updatedNotification = $this->repository->findUserNotification($notificationId, $userId) ?? $notification;

        return new NotificationResponse([
            'notification' => $this->mapper->mapNotification($updatedNotification)->toArray(),
            'unread_count' => $this->repository->countUnread($userId),
        ]);
    }

    public function markAllRead(NotificationRequest $request, int $userId): NotificationResponse
    {
        $markedCount = $this->repository->markAllRead($userId, $request->type());

        return new NotificationResponse(
            $this->transformer->markAllReadPayload($markedCount, $this->repository->countUnread($userId))
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function findNotificationOrFail(int $notificationId, int $userId): array
    {
        $notification = $this->repository->findUserNotification($notificationId, $userId);

        if ($notification === null) {
            throw new NotificationException(
                'Notification not found',
                404,
                NotificationException::CODE_NOTIFICATION_NOT_FOUND
            );
        }

        return $notification;
    }

    /**
     * @return array<string, int|bool>
     */
    private function pagination(NotificationRequest $request, int $total): array
    {
        $limit = $request->limit();
        $offset = $request->offset();

        return [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
            'has_more' => $offset + $limit < $total,
        ];
    }
}
