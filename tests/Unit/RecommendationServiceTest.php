<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Recommendation\Interfaces\RecommendationRepositoryInterface;
use App\Modules\Recommendation\Mappers\RecommendationMapper;
use App\Modules\Recommendation\Requests\RecommendationRequest;
use App\Modules\Recommendation\Services\RecommendationService;
use PHPUnit\Framework\TestCase;

final class RecommendationServiceTest extends TestCase
{
    public function testGuestGetsPopularRecommendationsWithoutCache(): void
    {
        $repository = $this->createMock(RecommendationRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findPopularMovies')
            ->with(20, null)
            ->willReturn([$this->movieRow()]);
        $repository
            ->expects($this->never())
            ->method('storeCache');

        $payload = $this->service($repository)->index(new RecommendationRequest())->toArray();

        self::assertSame('popular', $payload['source']);
        self::assertSame('Blade Runner 2049', $payload['items'][0]['movie']['title']);
        self::assertFalse($payload['cache']['hit']);
        self::assertNull($payload['cache']['expires_at']);
    }

    public function testAuthenticatedUserGetsFreshCachedRecommendations(): void
    {
        $request = new RecommendationRequest();
        $request->limit = 1;
        $repository = $this->createMock(RecommendationRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findFreshCache')
            ->with(7, 'personalized')
            ->willReturn([
                'source' => 'personalized',
                'payload_json' => [
                    'source' => 'personalized',
                    'generated_at' => '2026-05-21 10:00:00',
                    'items' => [
                        [
                            'rank' => 1,
                            'source' => 'personalized',
                            'reason' => 'similar_users',
                            'score' => 120.0,
                            'movie' => ['id' => 10, 'title' => 'Blade Runner 2049'],
                            'stats' => ['similar_users_count' => 2],
                        ],
                    ],
                ],
                'expires_at' => '2026-05-21 11:00:00',
            ]);
        $repository
            ->expects($this->never())
            ->method('findPersonalizedMovies');

        $payload = $this->service($repository)->index($request, 7)->toArray();

        self::assertSame('personalized', $payload['source']);
        self::assertSame('similar_users', $payload['items'][0]['reason']);
        self::assertTrue($payload['cache']['hit']);
        self::assertSame('2026-05-21 11:00:00', $payload['cache']['expires_at']);
    }

    public function testAuthenticatedUserBuildsAndStoresPersonalizedRecommendationsOnCacheMiss(): void
    {
        $repository = $this->createMock(RecommendationRepositoryInterface::class);
        $repository
            ->method('findFreshCache')
            ->with(7, 'personalized')
            ->willReturn(null);
        $repository
            ->expects($this->once())
            ->method('findPersonalizedMovies')
            ->with(7, 20)
            ->willReturn([$this->movieRow(['similar_users_count' => 3])]);
        $repository
            ->expects($this->once())
            ->method('storeCache')
            ->willReturnCallback(function (int $userId, string $source, array $payload, int $ttlSeconds): array {
                self::assertSame(7, $userId);
                self::assertSame('personalized', $source);
                self::assertSame(3600, $ttlSeconds);
                self::assertSame('Blade Runner 2049', $payload['items'][0]['movie']['title']);

                return [
                    'source' => $source,
                    'payload_json' => $payload,
                    'expires_at' => '2026-05-21 11:00:00',
                ];
            });

        $payload = $this->service($repository)->index(new RecommendationRequest(), 7)->toArray();

        self::assertSame('personalized', $payload['source']);
        self::assertSame('similar_users', $payload['items'][0]['reason']);
        self::assertFalse($payload['cache']['hit']);
    }

    public function testRefreshBypassesCacheAndUsesRequestedSource(): void
    {
        $request = new RecommendationRequest();
        $request->limit = 1;
        $request->source = RecommendationRequest::SOURCE_RECENT;
        $request->refresh = true;
        $repository = $this->createMock(RecommendationRepositoryInterface::class);
        $repository
            ->expects($this->never())
            ->method('findFreshCache');
        $repository
            ->expects($this->once())
            ->method('findRecentMovies')
            ->with(1, 7)
            ->willReturn([$this->movieRow()]);
        $repository
            ->expects($this->once())
            ->method('storeCache')
            ->willReturnCallback(fn (int $userId, string $source, array $payload): array => [
                'source' => $source,
                'payload_json' => $payload,
                'expires_at' => '2026-05-21 11:00:00',
            ]);

        $payload = $this->service($repository)->index($request, 7)->toArray();

        self::assertSame('recent', $payload['source']);
        self::assertFalse($payload['cache']['hit']);
    }

    public function testRebuildRegeneratesFullPersonalizedCache(): void
    {
        $repository = $this->createMock(RecommendationRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findPersonalizedMovies')
            ->with(7, 200)
            ->willReturn([$this->movieRow()]);
        $repository
            ->expects($this->once())
            ->method('storeCache')
            ->willReturn([
                'source' => 'personalized',
                'payload_json' => [],
                'expires_at' => '2026-05-21 11:00:00',
            ]);

        $payload = $this->service($repository)->rebuild(new RecommendationRequest(), 7)->toArray();

        self::assertTrue($payload['rebuilt']);
        self::assertSame('personalized', $payload['source']);
        self::assertSame('Blade Runner 2049', $payload['items'][0]['movie']['title']);
    }

    private function service(RecommendationRepositoryInterface $repository): RecommendationService
    {
        return new RecommendationService($repository, new RecommendationMapper());
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function movieRow(array $overrides = []): array
    {
        return $overrides + [
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
            'created_at' => '2026-05-18 10:00:00',
            'updated_at' => '2026-05-18 10:00:00',
            'ratings_count' => 5,
            'average_rating' => 8.4,
            'reviews_count' => 2,
            'views_count' => 4,
            'similar_users_count' => 0,
            'recommendation_score' => 88.5,
        ];
    }
}
