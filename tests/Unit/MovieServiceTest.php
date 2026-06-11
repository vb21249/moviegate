<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Movie\Exceptions\MovieException;
use App\Modules\Movie\Interfaces\MovieRepositoryInterface;
use App\Modules\Movie\Mappers\MovieMapper;
use App\Modules\Movie\Requests\MovieRequest;
use App\Modules\Movie\Services\MovieService;
use App\Modules\Movie\Transformers\MovieTransformer;
use PHPUnit\Framework\TestCase;

final class MovieServiceTest extends TestCase
{
    public function testViewReturnsMovieWithStats(): void
    {
        $repository = $this->createMock(MovieRepositoryInterface::class);
        $repository
            ->method('findPublishedMovie')
            ->with(10, null)
            ->willReturn($this->movieRow());
        $repository
            ->method('getMovieStats')
            ->with(10)
            ->willReturn([
                'ratings_count' => 5,
                'average_rating' => 8.4,
                'reviews_count' => 2,
                'comments_count' => 3,
                'views_count' => 4,
            ]);

        $payload = $this->service($repository)->view(10)->toArray();

        self::assertSame('Blade Runner 2049', $payload['movie']['title']);
        self::assertSame(8.4, $payload['movie']['stats']['average_rating']);
        self::assertArrayNotHasKey('deleted_at', $payload['movie']);
    }

    public function testIndexReturnsPaginatedMovies(): void
    {
        $repository = $this->createMock(MovieRepositoryInterface::class);
        $repository
            ->method('findPublishedMovies')
            ->with(20, 0, null, null)
            ->willReturn([$this->movieRow()]);
        $repository
            ->method('countPublishedMovies')
            ->with(null, null)
            ->willReturn(1);

        $payload = $this->service($repository)->index(new MovieRequest())->toArray();

        self::assertSame('Blade Runner 2049', $payload['items'][0]['title']);
        self::assertSame(1, $payload['pagination']['total']);
        self::assertFalse($payload['pagination']['has_more']);
    }

    public function testViewThrowsWhenMovieIsMissing(): void
    {
        $repository = $this->createMock(MovieRepositoryInterface::class);
        $repository
            ->method('findPublishedMovie')
            ->with(404, null)
            ->willReturn(null);

        $this->expectException(MovieException::class);
        $this->expectExceptionMessage('Movie not found');

        $this->service($repository)->view(404);
    }

    public function testWatchMarksMovieForAuthenticatedUser(): void
    {
        $repository = $this->createMock(MovieRepositoryInterface::class);
        $repository
            ->method('findPublishedMovie')
            ->with(10, null)
            ->willReturn($this->movieRow());
        $repository
            ->expects($this->once())
            ->method('markWatched')
            ->with(10, 7);

        $payload = $this->service($repository)->watch(10, 7)->toArray();

        self::assertTrue($payload['watched']);
        self::assertSame(10, $payload['movie_id']);
        self::assertSame(7, $payload['user_id']);
    }

    public function testIndexPassesRequestedLanguageAndReturnsLocalizedMovie(): void
    {
        $repository = $this->createMock(MovieRepositoryInterface::class);
        $request = new MovieRequest();
        $request->language = 'uk-UA';

        $repository
            ->expects($this->once())
            ->method('findPublishedMovies')
            ->with(20, 0, null, 'uk-UA')
            ->willReturn([array_merge($this->movieRow(), [
                'title' => 'Той, хто біжить по лезу 2049',
                'overview' => 'Молодий блейд-раннер відкриває давню таємницю.',
                'language' => 'uk-UA',
            ])]);
        $repository
            ->expects($this->once())
            ->method('countPublishedMovies')
            ->with(null, 'uk-UA')
            ->willReturn(1);

        $payload = $this->service($repository)->index($request)->toArray();

        self::assertSame('Той, хто біжить по лезу 2049', $payload['items'][0]['title']);
        self::assertSame('uk-UA', $payload['items'][0]['language']);
    }

    private function service(MovieRepositoryInterface $repository): MovieService
    {
        return new MovieService($repository, new MovieMapper(), new MovieTransformer());
    }

    /**
     * @return array<string, mixed>
     */
    private function movieRow(): array
    {
        return [
            'id' => 10,
            'tmdb_id' => 335984,
            'slug' => 'blade-runner-2049',
            'title' => 'Blade Runner 2049',
            'original_title' => 'Blade Runner 2049',
            'overview' => 'A young blade runner discovers a long-buried secret.',
            'poster_url' => 'https://example.com/poster.jpg',
            'backdrop_url' => 'https://example.com/backdrop.jpg',
            'release_date' => '2017-10-06',
            'runtime_minutes' => 164,
            'status' => 'active',
            'language' => 'en-US',
            'created_at' => '2026-05-18 10:00:00',
            'updated_at' => '2026-05-18 10:00:00',
        ];
    }
}
