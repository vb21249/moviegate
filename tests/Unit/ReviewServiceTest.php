<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Review\Exceptions\ReviewException;
use App\Modules\Review\Interfaces\ReviewRepositoryInterface;
use App\Modules\Review\Mappers\ReviewMapper;
use App\Modules\Review\Requests\ReviewRequest;
use App\Modules\Review\Services\ReviewService;
use App\Modules\Review\Transformers\ReviewTransformer;
use PHPUnit\Framework\TestCase;

final class ReviewServiceTest extends TestCase
{
    public function testIndexReturnsPaginatedPublishedReviews(): void
    {
        $repository = $this->createMock(ReviewRepositoryInterface::class);
        $repository
            ->method('findPublishedReviews')
            ->with(20, 0, null, null, null)
            ->willReturn([$this->reviewRow()]);
        $repository
            ->method('countPublishedReviews')
            ->with(null, null, null)
            ->willReturn(1);

        $payload = $this->service($repository)->index(new ReviewRequest())->toArray();

        self::assertSame('Excellent sequel', $payload['items'][0]['title']);
        self::assertSame('Blade Runner 2049', $payload['items'][0]['movie']['title']);
        self::assertSame(1, $payload['pagination']['total']);
    }

    public function testViewThrowsWhenReviewIsMissing(): void
    {
        $repository = $this->createMock(ReviewRepositoryInterface::class);
        $repository
            ->method('findPublishedReview')
            ->with(404)
            ->willReturn(null);

        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('Review not found');

        $this->service($repository)->view(404);
    }

    public function testCreatePersistsReviewForActiveMovie(): void
    {
        $repository = $this->createMock(ReviewRepositoryInterface::class);
        $repository
            ->method('movieExists')
            ->with(10)
            ->willReturn(true);
        $repository
            ->expects($this->once())
            ->method('createReview')
            ->with(7, self::callback(
                static fn (array $attributes): bool => $attributes['movie_id'] === 10
                    && $attributes['title'] === 'Excellent sequel'
                    && $attributes['status'] === 'published'
            ))
            ->willReturn($this->reviewRow());

        $request = new ReviewRequest(['scenario' => ReviewRequest::SCENARIO_CREATE]);
        $request->loadFromArray([
            'movie_id' => 10,
            'title' => 'Excellent sequel',
            'body' => 'A thoughtful continuation with strong visual design.',
        ]);

        $payload = $this->service($repository)->create($request, 7)->toArray();

        self::assertSame(15, $payload['review']['id']);
        self::assertSame('published', $payload['review']['status']);
    }

    public function testCreateThrowsWhenMovieIsMissing(): void
    {
        $repository = $this->createMock(ReviewRepositoryInterface::class);
        $repository
            ->method('movieExists')
            ->with(10)
            ->willReturn(false);

        $request = new ReviewRequest(['scenario' => ReviewRequest::SCENARIO_CREATE]);
        $request->loadFromArray([
            'movie_id' => 10,
            'title' => 'Excellent sequel',
            'body' => 'A thoughtful continuation with strong visual design.',
        ]);

        $this->expectException(ReviewException::class);
        $this->expectExceptionMessage('Movie not found');

        $this->service($repository)->create($request, 7);
    }

    public function testUpdateOwnedReview(): void
    {
        $repository = $this->createMock(ReviewRepositoryInterface::class);
        $repository
            ->method('findOwnedReview')
            ->with(15, 7)
            ->willReturn($this->reviewRow(), array_merge($this->reviewRow(), ['title' => 'Updated title']));
        $repository
            ->method('movieExists')
            ->with(10)
            ->willReturn(true);
        $repository
            ->expects($this->once())
            ->method('updateReview')
            ->with(15, 7, ['title' => 'Updated title']);

        $request = new ReviewRequest(['scenario' => ReviewRequest::SCENARIO_UPDATE]);
        $request->loadFromArray(['title' => 'Updated title']);

        $payload = $this->service($repository)->update(15, $request, 7)->toArray();

        self::assertSame('Updated title', $payload['review']['title']);
    }

    public function testDeleteOwnedReview(): void
    {
        $repository = $this->createMock(ReviewRepositoryInterface::class);
        $repository
            ->method('findOwnedReview')
            ->with(15, 7)
            ->willReturn($this->reviewRow());
        $repository
            ->expects($this->once())
            ->method('softDeleteReview')
            ->with(15, 7);

        $payload = $this->service($repository)->delete(15, 7)->toArray();

        self::assertTrue($payload['deleted']);
        self::assertSame(15, $payload['review_id']);
    }

    private function service(ReviewRepositoryInterface $repository): ReviewService
    {
        return new ReviewService($repository, new ReviewMapper(), new ReviewTransformer());
    }

    /**
     * @return array<string, mixed>
     */
    private function reviewRow(): array
    {
        return [
            'id' => 15,
            'user_id' => 7,
            'movie_id' => 10,
            'rating_id' => null,
            'title' => 'Excellent sequel',
            'body' => 'A thoughtful continuation with strong visual design.',
            'likes_count' => 3,
            'status' => 'published',
            'published_at' => '2026-05-18 12:00:00',
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
