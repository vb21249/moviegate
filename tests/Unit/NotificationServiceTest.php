<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Notification\Exceptions\NotificationException;
use App\Modules\Notification\Interfaces\NotificationRepositoryInterface;
use App\Modules\Notification\Mappers\NotificationMapper;
use App\Modules\Notification\Requests\NotificationRequest;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Notification\Transformers\NotificationTransformer;
use PHPUnit\Framework\TestCase;

final class NotificationServiceTest extends TestCase
{
    public function testIndexReturnsUserNotificationsWithUnreadCount(): void
    {
        $repository = $this->createMock(NotificationRepositoryInterface::class);
        $repository
            ->method('findUserNotifications')
            ->with(7, 20, 0, null, false)
            ->willReturn([$this->notificationRow()]);
        $repository
            ->method('countUserNotifications')
            ->with(7, null, false)
            ->willReturn(1);
        $repository
            ->method('countUnread')
            ->with(7)
            ->willReturn(1);

        $payload = $this->service($repository)->index(new NotificationRequest(), 7)->toArray();

        self::assertSame('comment_created', $payload['items'][0]['type']);
        self::assertSame('Blade Runner 2049', $payload['items'][0]['payload']['movie_title']);
        self::assertFalse($payload['items'][0]['is_read']);
        self::assertSame(1, $payload['unread_count']);
    }

    public function testViewThrowsWhenNotificationIsMissing(): void
    {
        $repository = $this->createMock(NotificationRepositoryInterface::class);
        $repository
            ->method('findUserNotification')
            ->with(404, 7)
            ->willReturn(null);

        $this->expectException(NotificationException::class);
        $this->expectExceptionMessage('Notification not found');

        $this->service($repository)->view(404, 7);
    }

    public function testMarkReadReturnsUpdatedNotification(): void
    {
        $repository = $this->createMock(NotificationRepositoryInterface::class);
        $repository
            ->method('findUserNotification')
            ->with(51, 7)
            ->willReturn(
                $this->notificationRow(),
                array_merge($this->notificationRow(), ['read_at' => '2026-05-20 12:00:00'])
            );
        $repository
            ->expects($this->once())
            ->method('markRead')
            ->with(51, 7);
        $repository
            ->method('countUnread')
            ->with(7)
            ->willReturn(0);

        $payload = $this->service($repository)->markRead(51, 7)->toArray();

        self::assertTrue($payload['notification']['is_read']);
        self::assertSame(0, $payload['unread_count']);
    }

    public function testMarkAllReadReturnsCounterPayload(): void
    {
        $repository = $this->createMock(NotificationRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('markAllRead')
            ->with(7, 'comment_created')
            ->willReturn(3);
        $repository
            ->method('countUnread')
            ->with(7)
            ->willReturn(0);

        $request = new NotificationRequest(['scenario' => NotificationRequest::SCENARIO_MARK_ALL_READ]);
        $request->loadFromArray(['type' => 'comment_created']);

        $payload = $this->service($repository)->markAllRead($request, 7)->toArray();

        self::assertSame(3, $payload['marked_read']);
        self::assertSame(0, $payload['unread_count']);
    }

    private function service(NotificationRepositoryInterface $repository): NotificationService
    {
        return new NotificationService($repository, new NotificationMapper(), new NotificationTransformer());
    }

    /**
     * @return array<string, mixed>
     */
    private function notificationRow(): array
    {
        return [
            'id' => 51,
            'user_id' => 7,
            'type' => 'comment_created',
            'payload_json' => '{"comment_id":21,"movie_title":"Blade Runner 2049"}',
            'read_at' => null,
            'created_at' => '2026-05-20 11:00:00',
            'updated_at' => '2026-05-20 11:00:00',
        ];
    }
}
