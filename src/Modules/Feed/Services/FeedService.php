<?php

declare(strict_types=1);

namespace App\Modules\Feed\Services;

use App\Common\Services\AbstractService;
use App\Modules\Feed\Exceptions\FeedException;
use App\Modules\Feed\Interfaces\FeedRepositoryInterface;
use App\Modules\Feed\Interfaces\FeedServiceInterface;
use App\Modules\Feed\Mappers\FeedMapper;
use App\Modules\Feed\Requests\FeedRequest;
use App\Modules\Feed\Responses\FeedResponse;

/**
 * Feed application service for public activity stream reads.
 */
final class FeedService extends AbstractService implements FeedServiceInterface
{
    public function __construct(
        private readonly FeedRepositoryInterface $repository,
        private readonly FeedMapper $mapper,
    ) {
    }

    public function index(FeedRequest $request): FeedResponse
    {
        $events = array_map(
            fn (array $event): array => $this->mapper->mapEvent($event)->toArray(),
            $this->repository->findEvents(
                $request->limit(),
                $request->offset(),
                $request->userId(),
                $request->eventType(),
                $request->entityType(),
                $request->entityId()
            )
        );

        return new FeedResponse([
            'items' => $events,
            'pagination' => $this->pagination(
                $request,
                $this->repository->countEvents(
                    $request->userId(),
                    $request->eventType(),
                    $request->entityType(),
                    $request->entityId()
                )
            ),
        ]);
    }

    public function view(int $eventId): FeedResponse
    {
        return new FeedResponse([
            'event' => $this->mapper->mapEvent($this->findEventOrFail($eventId))->toArray(),
        ]);
    }

    public function mine(FeedRequest $request, int $userId): FeedResponse
    {
        $events = array_map(
            fn (array $event): array => $this->mapper->mapEvent($event)->toArray(),
            $this->repository->findUserEvents(
                $userId,
                $request->limit(),
                $request->offset(),
                $request->eventType(),
                $request->entityType(),
                $request->entityId()
            )
        );

        return new FeedResponse([
            'items' => $events,
            'pagination' => $this->pagination(
                $request,
                $this->repository->countUserEvents(
                    $userId,
                    $request->eventType(),
                    $request->entityType(),
                    $request->entityId()
                )
            ),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function findEventOrFail(int $eventId): array
    {
        $event = $this->repository->findEvent($eventId);

        if ($event === null) {
            throw new FeedException('Feed event not found', 404, FeedException::CODE_FEED_EVENT_NOT_FOUND);
        }

        return $event;
    }

    /**
     * @return array<string, int|bool>
     */
    private function pagination(FeedRequest $request, int $total): array
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
