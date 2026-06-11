<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Events\DomainEventSubscriberRegistry;
use App\Common\Events\SyncDomainEventDispatcher;
use App\Modules\Comment\Events\CommentEvent;
use App\Modules\Feed\Interfaces\FeedRepositoryInterface;
use App\Modules\Feed\Subscribers\SocialActivityFeedSubscriber;
use App\Modules\Notification\Interfaces\NotificationRepositoryInterface;
use App\Modules\Notification\Subscribers\SocialActivityNotificationSubscriber;
use App\Modules\Rating\Events\RatingEvent;
use PHPUnit\Framework\TestCase;

final class DomainEventFlowTest extends TestCase
{
    public function testDispatcherProjectsActivityEventIntoFeed(): void
    {
        $repository = $this->createMock(FeedRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('appendEvent')
            ->with(
                7,
                RatingEvent::CREATED,
                'rating',
                31,
                self::callback(static fn (array $payload): bool => $payload['score'] === 9
                    && $payload['movie_title'] === 'Blade Runner 2049'
                    && isset($payload['event_occurred_at']))
            )
            ->willReturn([]);

        $dispatcher = new SyncDomainEventDispatcher(new DomainEventSubscriberRegistry([
            new SocialActivityFeedSubscriber($repository),
        ]));

        $dispatcher->dispatch(new RatingEvent(RatingEvent::CREATED, 7, 31, [
            'rating_id' => 31,
            'movie_title' => 'Blade Runner 2049',
            'score' => 9,
        ]));
    }

    public function testNotificationSubscriberCreatesRecipientNotification(): void
    {
        $repository = $this->createMock(NotificationRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('createNotification')
            ->with(
                7,
                CommentEvent::REPLIED,
                self::callback(static fn (array $payload): bool => $payload['actor_user_id'] === 8
                    && $payload['entity_type'] === 'comment'
                    && $payload['entity_id'] === 22
                    && $payload['parent_comment_id'] === 21
                    && !array_key_exists('recipient_user_id', $payload))
            )
            ->willReturn([]);

        $subscriber = new SocialActivityNotificationSubscriber($repository);
        $subscriber->handle(new CommentEvent(CommentEvent::REPLIED, 8, 22, [
            'comment_id' => 22,
            'parent_comment_id' => 21,
            'recipient_user_id' => 7,
        ]));
    }
}
