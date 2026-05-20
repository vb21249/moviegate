<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Services;

use App\Common\Services\AbstractService;
use App\Modules\Recommendation\Interfaces\RecommendationRepositoryInterface;
use App\Modules\Recommendation\Interfaces\RecommendationServiceInterface;
use App\Modules\Recommendation\Mappers\RecommendationMapper;
use App\Modules\Recommendation\Requests\RecommendationRequest;
use App\Modules\Recommendation\Responses\RecommendationResponse;

/**
 * Recommendation application service for cached catalog suggestions.
 */
final class RecommendationService extends AbstractService implements RecommendationServiceInterface
{
    private const CACHE_TTL_SECONDS = 3600;
    private const MAX_CACHE_ITEMS = 200;

    public function __construct(
        private readonly RecommendationRepositoryInterface $repository,
        private readonly RecommendationMapper $mapper,
    ) {
    }

    public function index(RecommendationRequest $request, ?int $userId = null): RecommendationResponse
    {
        $source = $this->resolveSource($request->source(), $userId);
        $neededItems = $this->neededItems($request);
        $cache = $userId !== null && !$request->refresh()
            ? $this->repository->findFreshCache($userId, $source)
            : null;

        if ($cache !== null && $this->cacheCoversRequest($cache, $request)) {
            return $this->responseFromPayload(
                $this->cachePayload($cache),
                $request,
                true,
                $cache['expires_at'] !== null ? (string) $cache['expires_at'] : null
            );
        }

        $payload = $this->buildPayload($source, $userId, $neededItems);
        $expiresAt = null;

        if ($userId !== null) {
            $cache = $this->repository->storeCache($userId, $source, $payload, self::CACHE_TTL_SECONDS);
            $expiresAt = $cache['expires_at'] !== null ? (string) $cache['expires_at'] : null;
        }

        return $this->responseFromPayload($payload, $request, false, $expiresAt);
    }

    public function rebuild(RecommendationRequest $request, int $userId): RecommendationResponse
    {
        $source = $this->resolveSource($request->source(), $userId);
        $payload = $this->buildPayload($source, $userId, self::MAX_CACHE_ITEMS);
        $cache = $this->repository->storeCache($userId, $source, $payload, self::CACHE_TTL_SECONDS);

        return $this->responseFromPayload(
            $payload + ['rebuilt' => true],
            $request,
            false,
            $cache['expires_at'] !== null ? (string) $cache['expires_at'] : null
        );
    }

    private function resolveSource(?string $requestedSource, ?int $userId): string
    {
        $source = $requestedSource ?? (
            $userId === null
                ? RecommendationRequest::SOURCE_POPULAR
                : RecommendationRequest::SOURCE_PERSONALIZED
        );

        if ($source === RecommendationRequest::SOURCE_PERSONALIZED && $userId === null) {
            return RecommendationRequest::SOURCE_POPULAR;
        }

        return $source;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(string $source, ?int $userId, int $limit): array
    {
        $rows = match ($source) {
            RecommendationRequest::SOURCE_RECENT => $this->repository->findRecentMovies($limit, $userId),
            RecommendationRequest::SOURCE_PERSONALIZED => $userId !== null
                ? $this->repository->findPersonalizedMovies($userId, $limit)
                : $this->repository->findPopularMovies($limit),
            default => $this->repository->findPopularMovies($limit, $userId),
        };

        $rank = 1;
        $items = array_map(
            function (array $row) use ($source, &$rank): array {
                return $this->mapper->mapRecommendation($row, $source, $rank++)->toArray();
            },
            $rows
        );

        return [
            'source' => $source,
            'generated_at' => date('Y-m-d H:i:s'),
            'max_items' => $limit,
            'items' => $items,
        ];
    }

    private function neededItems(RecommendationRequest $request): int
    {
        return min(self::MAX_CACHE_ITEMS, max($request->limit(), $request->offset() + $request->limit()));
    }

    /**
     * @param array<string, mixed> $cache
     */
    private function cacheCoversRequest(array $cache, RecommendationRequest $request): bool
    {
        $payload = $this->cachePayload($cache);
        $items = $payload['items'] ?? [];

        if (!is_array($items)) {
            return false;
        }

        $itemsCount = count($items);
        $maxItems = (int) ($payload['max_items'] ?? $itemsCount);

        return $itemsCount >= $this->neededItems($request)
            || $itemsCount < $maxItems
            || $maxItems >= self::MAX_CACHE_ITEMS;
    }

    /**
     * @param array<string, mixed> $cache
     *
     * @return array<string, mixed>
     */
    private function cachePayload(array $cache): array
    {
        $payload = $cache['payload_json'] ?? [];

        return is_array($payload) ? $payload : [];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function responseFromPayload(
        array $payload,
        RecommendationRequest $request,
        bool $cacheHit,
        ?string $expiresAt
    ): RecommendationResponse {
        $items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
        $pagedItems = array_values(array_slice($items, $request->offset(), $request->limit()));
        $total = count($items);

        return new RecommendationResponse([
            'source' => (string) ($payload['source'] ?? RecommendationRequest::SOURCE_POPULAR),
            'generated_at' => $payload['generated_at'] ?? null,
            'items' => $pagedItems,
            'pagination' => [
                'limit' => $request->limit(),
                'offset' => $request->offset(),
                'total' => $total,
                'has_more' => $request->offset() + $request->limit() < $total,
            ],
            'cache' => [
                'hit' => $cacheHit,
                'expires_at' => $expiresAt,
            ],
            'rebuilt' => (bool) ($payload['rebuilt'] ?? false),
        ]);
    }
}
