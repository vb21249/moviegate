<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Events\DomainEventBusInterface;
use App\Modules\Comment\Events\CommentEvent;
use App\Modules\Comment\Exceptions\CommentException;
use App\Modules\Comment\Interfaces\CommentRepositoryInterface;
use App\Modules\Comment\Mappers\CommentMapper;
use App\Modules\Comment\Requests\CommentRequest;
use App\Modules\Comment\Services\CommentService;
use App\Modules\Comment\Transformers\CommentTransformer;
use PHPUnit\Framework\TestCase;
use Tests\Support\CapturingDomainEventBus;

final class CommentServiceTest extends TestCase
{
    public function testIndexReturnsPaginatedTopLevelComments(): void
    {
        $repository = $this->createMock(CommentRepositoryInterface::class);
        $repository
            ->method('findComments')
            ->with(20, 0, null, null, null, null)
            ->willReturn([$this->commentRow()]);
        $repository
            ->method('countComments')
            ->with(null, null, null, null)
            ->willReturn(1);

        $payload = $this->service($repository)->index(new CommentRequest())->toArray();

        self::assertSame('Strong scene work.', $payload['items'][0]['body']);
        self::assertSame('Blade Runner 2049', $payload['items'][0]['movie']['title']);
        self::assertSame(1, $payload['pagination']['total']);
    }

    public function testViewThrowsWhenCommentIsMissing(): void
    {
        $repository = $this->createMock(CommentRepositoryInterface::class);
        $repository
            ->method('findComment')
            ->with(404)
            ->willReturn(null);

        $this->expectException(CommentException::class);
        $this->expectExceptionMessage('Comment not found');

        $this->service($repository)->view(404);
    }

    public function testCreatePersistsMovieComment(): void
    {
        $repository = $this->createMock(CommentRepositoryInterface::class);
        $repository
            ->method('movieExists')
            ->with(10)
            ->willReturn(true);
        $repository
            ->expects($this->once())
            ->method('createComment')
            ->with(7, self::callback(
                static fn (array $attributes): bool => $attributes['movie_id'] === 10
                    && $attributes['body'] === 'Strong scene work.'
            ))
            ->willReturn($this->commentRow());

        $request = new CommentRequest(['scenario' => CommentRequest::SCENARIO_CREATE]);
        $request->loadFromArray([
            'movie_id' => 10,
            'body' => 'Strong scene work.',
        ]);

        $payload = $this->service($repository)->create($request, 7)->toArray();

        self::assertSame(21, $payload['comment']['id']);
        self::assertSame(10, $payload['comment']['movie_id']);
    }

    public function testCreateRequiresExactlyOneTarget(): void
    {
        $repository = $this->createMock(CommentRepositoryInterface::class);
        $request = new CommentRequest(['scenario' => CommentRequest::SCENARIO_CREATE]);
        $request->loadFromArray(['body' => 'Strong scene work.']);

        $this->expectException(CommentException::class);
        $this->expectExceptionMessage('Comment must target exactly one movie or review');

        $this->service($repository)->create($request, 7);
    }

    public function testReplyCopiesParentTarget(): void
    {
        $repository = $this->createMock(CommentRepositoryInterface::class);
        $repository
            ->method('findComment')
            ->with(21)
            ->willReturn($this->commentRow());
        $repository
            ->expects($this->once())
            ->method('createComment')
            ->with(8, self::callback(
                static fn (array $attributes): bool => $attributes['parent_id'] === 21
                    && $attributes['movie_id'] === 10
                    && $attributes['review_id'] === null
                    && $attributes['body'] === 'I agree.'
            ))
            ->willReturn(array_merge($this->commentRow(), [
                'id' => 22,
                'user_id' => 8,
                'parent_id' => 21,
                'body' => 'I agree.',
            ]));

        $request = new CommentRequest(['scenario' => CommentRequest::SCENARIO_REPLY]);
        $request->loadFromArray(['body' => 'I agree.']);

        $eventBus = new CapturingDomainEventBus();
        $payload = $this->service($repository, $eventBus)->reply(21, $request, 8)->toArray();

        self::assertSame(21, $payload['comment']['parent_id']);
        self::assertSame('I agree.', $payload['comment']['body']);
        self::assertCount(1, $eventBus->events);
        self::assertInstanceOf(CommentEvent::class, $eventBus->events[0]);
        self::assertSame(CommentEvent::REPLIED, $eventBus->events[0]->eventName());
        self::assertSame(8, $eventBus->events[0]->actorId());
        self::assertSame(22, $eventBus->events[0]->entityId());
        self::assertSame(7, $eventBus->events[0]->payload()['recipient_user_id']);
    }

    public function testUpdateOwnedComment(): void
    {
        $repository = $this->createMock(CommentRepositoryInterface::class);
        $repository
            ->method('findOwnedComment')
            ->with(21, 7)
            ->willReturn($this->commentRow(), array_merge($this->commentRow(), ['body' => 'Updated comment.']));
        $repository
            ->expects($this->once())
            ->method('updateComment')
            ->with(21, 7, ['body' => 'Updated comment.']);

        $request = new CommentRequest(['scenario' => CommentRequest::SCENARIO_UPDATE]);
        $request->loadFromArray(['body' => 'Updated comment.']);

        $payload = $this->service($repository)->update(21, $request, 7)->toArray();

        self::assertSame('Updated comment.', $payload['comment']['body']);
    }

    public function testDeleteOwnedComment(): void
    {
        $repository = $this->createMock(CommentRepositoryInterface::class);
        $repository
            ->method('findOwnedComment')
            ->with(21, 7)
            ->willReturn($this->commentRow());
        $repository
            ->expects($this->once())
            ->method('softDeleteComment')
            ->with(21, 7);

        $payload = $this->service($repository)->delete(21, 7)->toArray();

        self::assertTrue($payload['deleted']);
        self::assertSame(21, $payload['comment_id']);
    }

    public function testLikeCommentReturnsUpdatedCounter(): void
    {
        $repository = $this->createMock(CommentRepositoryInterface::class);
        $repository
            ->method('findComment')
            ->with(21)
            ->willReturn($this->commentRow());
        $repository
            ->expects($this->once())
            ->method('likeComment')
            ->with(21, 8)
            ->willReturn(array_merge($this->commentRow(), ['likes_count' => 4]));

        $payload = $this->service($repository)->like(21, 8)->toArray();

        self::assertTrue($payload['liked']);
        self::assertSame(4, $payload['likes_count']);
    }

    private function service(
        CommentRepositoryInterface $repository,
        ?DomainEventBusInterface $eventBus = null
    ): CommentService {
        return new CommentService($repository, new CommentMapper(), new CommentTransformer(), $eventBus);
    }

    /**
     * @return array<string, mixed>
     */
    private function commentRow(): array
    {
        return [
            'id' => 21,
            'user_id' => 7,
            'movie_id' => 10,
            'review_id' => null,
            'parent_id' => null,
            'body' => 'Strong scene work.',
            'likes_count' => 3,
            'created_at' => '2026-05-18 12:00:00',
            'updated_at' => '2026-05-18 12:00:00',
            'username' => 'student',
            'user_avatar_url' => null,
            'movie_slug' => 'blade-runner-2049',
            'movie_title' => 'Blade Runner 2049',
            'movie_poster_url' => 'https://example.com/poster.jpg',
            'movie_release_date' => '2017-10-06',
            'review_title' => null,
            'review_movie_id' => null,
        ];
    }
}
