<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Feed\Exceptions\FeedException;
use App\Modules\Feed\Interfaces\FeedRepositoryInterface;
use App\Modules\Feed\Mappers\FeedMapper;
use App\Modules\Feed\Requests\FeedRequest;
use App\Modules\Feed\Services\FeedService;
use PHPUnit\Framework\TestCase;

final class FeedServiceTest extends TestCase
{
    public function testIndexReturnsPaginatedFeedEvents(): void
    {
        $repository = $this->createMock(FeedRepositoryInterface::class);
        $repository
            ->method('findEvents')
            ->with(20, 0, null, null, null, null)
            ->willReturn([$this->feedRow()]);
        $repository
            ->method('countEvents')
            ->with(null, null, null, null)
            ->willReturn(1);

        $payload = $this->service($repository)->index(new FeedRequest())->toArray();

        self::assertSame('rating_created', $payload['items'][0]['event_type']);
        self::assertSame('Blade Runner 2049', $payload['items'][0]['payload']['movie_title']);
        self::assertSame('student', $payload['items'][0]['user']['username']);
        self::assertSame(1, $payload['pagination']['total']);
    }

    public function testViewThrowsWhenFeedEventIsMissing(): void
    {
        $repository = $this->createMock(FeedRepositoryInterface::class);
        $repository
            ->method('findEvent')
            ->with(404)
            ->willReturn(null);

        $this->expectException(FeedException::class);
        $this->expectExceptionMessage('Feed event not found');

        $this->service($repository)->view(404);
    }

    public function testMineReturnsCurrentUserEventsWithFilters(): void
    {
        $repository = $this->createMock(FeedRepositoryInterface::class);
        $repository
            ->method('findUserEvents')
            ->with(7, 20, 0, 'rating_created', 'rating', null)
            ->willReturn([$this->feedRow()]);
        $repository
            ->method('countUserEvents')
            ->with(7, 'rating_created', 'rating', null)
            ->willReturn(1);

        $request = new FeedRequest(['scenario' => FeedRequest::SCENARIO_MINE]);
        $request->loadFromArray([
            'event_type' => 'rating_created',
            'entity_type' => 'rating',
        ]);

        $payload = $this->service($repository)->mine($request, 7)->toArray();

        self::assertSame(41, $payload['items'][0]['id']);
        self::assertSame('rating', $payload['items'][0]['entity_type']);
        self::assertSame(1, $payload['pagination']['total']);
    }

    private function service(FeedRepositoryInterface $repository): FeedService
    {
        return new FeedService($repository, new FeedMapper());
    }

    /**
     * @return array<string, mixed>
     */
    private function feedRow(): array
    {
        return [
            'id' => 41,
            'user_id' => 7,
            'event_type' => 'rating_created',
            'entity_type' => 'rating',
            'entity_id' => 31,
            'payload_json' => '{"movie_id":10,"movie_title":"Blade Runner 2049","score":9}',
            'occurred_at' => '2026-05-19 12:00:00',
            'created_at' => '2026-05-19 12:00:00',
            'updated_at' => '2026-05-19 12:00:00',
            'username' => 'student',
            'user_avatar_url' => null,
        ];
    }
}
