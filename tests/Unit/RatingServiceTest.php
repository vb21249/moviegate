<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Events\DomainEventBusInterface;
use App\Modules\Rating\Events\RatingEvent;
use App\Modules\Rating\Exceptions\RatingException;
use App\Modules\Rating\Interfaces\RatingRepositoryInterface;
use App\Modules\Rating\Mappers\RatingMapper;
use App\Modules\Rating\Requests\RatingRequest;
use App\Modules\Rating\Services\RatingService;
use App\Modules\Rating\Transformers\RatingTransformer;
use PHPUnit\Framework\TestCase;
use Tests\Support\CapturingDomainEventBus;

final class RatingServiceTest extends TestCase
{
    public function testIndexReturnsPaginatedRatings(): void
    {
        $repository = $this->createMock(RatingRepositoryInterface::class);
        $repository
            ->method('findRatings')
            ->with(20, 0, null, null, null)
            ->willReturn([$this->ratingRow()]);
        $repository
            ->method('countRatings')
            ->with(null, null, null)
            ->willReturn(1);

        $payload = $this->service($repository)->index(new RatingRequest())->toArray();

        self::assertSame(9, $payload['items'][0]['score']);
        self::assertSame('Blade Runner 2049', $payload['items'][0]['movie']['title']);
        self::assertSame(1, $payload['pagination']['total']);
    }

    public function testViewThrowsWhenRatingIsMissing(): void
    {
        $repository = $this->createMock(RatingRepositoryInterface::class);
        $repository
            ->method('findRating')
            ->with(404)
            ->willReturn(null);

        $this->expectException(RatingException::class);
        $this->expectExceptionMessage('Rating not found');

        $this->service($repository)->view(404);
    }

    public function testCreatePersistsRatingForActiveMovie(): void
    {
        $repository = $this->createMock(RatingRepositoryInterface::class);
        $repository
            ->method('movieExists')
            ->with(10)
            ->willReturn(true);
        $repository
            ->method('findOwnedRatingForMovie')
            ->with(7, 10)
            ->willReturn(null);
        $repository
            ->expects($this->once())
            ->method('createRating')
            ->with(7, self::callback(
                static fn (array $attributes): bool => $attributes['movie_id'] === 10
                    && $attributes['score'] === 9
                    && $attributes['review_text'] === 'Great atmosphere.'
            ))
            ->willReturn($this->ratingRow());

        $request = new RatingRequest(['scenario' => RatingRequest::SCENARIO_CREATE]);
        $request->loadFromArray([
            'movie_id' => 10,
            'score' => 9,
            'review_text' => 'Great atmosphere.',
        ]);

        $eventBus = new CapturingDomainEventBus();
        $payload = $this->service($repository, $eventBus)->create($request, 7)->toArray();

        self::assertSame(31, $payload['rating']['id']);
        self::assertSame(9, $payload['rating']['score']);
        self::assertCount(1, $eventBus->events);
        self::assertInstanceOf(RatingEvent::class, $eventBus->events[0]);
        self::assertSame(RatingEvent::CREATED, $eventBus->events[0]->eventName());
        self::assertSame(7, $eventBus->events[0]->actorId());
        self::assertSame(31, $eventBus->events[0]->entityId());
        self::assertSame(9, $eventBus->events[0]->payload()['score']);
    }

    public function testCreateUpdatesExistingUserMovieRating(): void
    {
        $repository = $this->createMock(RatingRepositoryInterface::class);
        $repository
            ->method('movieExists')
            ->with(10)
            ->willReturn(true);
        $repository
            ->method('findOwnedRatingForMovie')
            ->with(7, 10)
            ->willReturn($this->ratingRow());
        $repository
            ->expects($this->once())
            ->method('updateRating')
            ->with(31, 7, [
                'score' => 8,
                'review_text' => 'Still strong.',
            ]);
        $repository
            ->method('findOwnedRating')
            ->with(31, 7)
            ->willReturn(array_merge($this->ratingRow(), [
                'score' => 8,
                'review_text' => 'Still strong.',
            ]));

        $request = new RatingRequest(['scenario' => RatingRequest::SCENARIO_CREATE]);
        $request->loadFromArray([
            'movie_id' => 10,
            'score' => 8,
            'review_text' => 'Still strong.',
        ]);

        $payload = $this->service($repository)->create($request, 7)->toArray();

        self::assertSame(8, $payload['rating']['score']);
        self::assertSame('Still strong.', $payload['rating']['review_text']);
    }

    public function testCreateThrowsWhenMovieIsMissing(): void
    {
        $repository = $this->createMock(RatingRepositoryInterface::class);
        $repository
            ->method('movieExists')
            ->with(10)
            ->willReturn(false);

        $request = new RatingRequest(['scenario' => RatingRequest::SCENARIO_CREATE]);
        $request->loadFromArray([
            'movie_id' => 10,
            'score' => 9,
        ]);

        $this->expectException(RatingException::class);
        $this->expectExceptionMessage('Movie not found');

        $this->service($repository)->create($request, 7);
    }

    public function testUpdateOwnedRating(): void
    {
        $repository = $this->createMock(RatingRepositoryInterface::class);
        $repository
            ->method('findOwnedRating')
            ->with(31, 7)
            ->willReturn($this->ratingRow(), array_merge($this->ratingRow(), ['score' => 7]));
        $repository
            ->expects($this->once())
            ->method('updateRating')
            ->with(31, 7, ['score' => 7]);

        $request = new RatingRequest(['scenario' => RatingRequest::SCENARIO_UPDATE]);
        $request->loadFromArray(['score' => 7]);

        $payload = $this->service($repository)->update(31, $request, 7)->toArray();

        self::assertSame(7, $payload['rating']['score']);
    }

    public function testDeleteOwnedRating(): void
    {
        $repository = $this->createMock(RatingRepositoryInterface::class);
        $repository
            ->method('findOwnedRating')
            ->with(31, 7)
            ->willReturn($this->ratingRow());
        $repository
            ->expects($this->once())
            ->method('softDeleteRating')
            ->with(31, 7);

        $payload = $this->service($repository)->delete(31, 7)->toArray();

        self::assertTrue($payload['deleted']);
        self::assertSame(31, $payload['rating_id']);
    }

    public function testHistoryReturnsCurrentUserRatings(): void
    {
        $repository = $this->createMock(RatingRepositoryInterface::class);
        $repository
            ->method('findUserHistory')
            ->with(7, 20, 0, null)
            ->willReturn([$this->ratingRow()]);
        $repository
            ->method('countUserHistory')
            ->with(7, null)
            ->willReturn(1);

        $payload = $this->service($repository)->history(new RatingRequest(), 7)->toArray();

        self::assertSame(31, $payload['items'][0]['id']);
        self::assertSame(1, $payload['pagination']['total']);
    }

    private function service(
        RatingRepositoryInterface $repository,
        ?DomainEventBusInterface $eventBus = null
    ): RatingService {
        return new RatingService($repository, new RatingMapper(), new RatingTransformer(), $eventBus);
    }

    /**
     * @return array<string, mixed>
     */
    private function ratingRow(): array
    {
        return [
            'id' => 31,
            'user_id' => 7,
            'movie_id' => 10,
            'score' => 9,
            'review_text' => 'Great atmosphere.',
            'rated_at' => '2026-05-18 12:00:00',
            'created_at' => '2026-05-18 12:00:00',
            'updated_at' => '2026-05-18 12:00:00',
            'username' => 'student',
            'user_avatar_url' => null,
            'movie_slug' => 'blade-runner-2049',
            'movie_title' => 'Blade Runner 2049',
            'movie_poster_url' => 'https://example.com/poster.jpg',
            'movie_release_date' => '2017-10-06',
        ];
    }
}
