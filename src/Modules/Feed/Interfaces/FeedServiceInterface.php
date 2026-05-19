<?php

declare(strict_types=1);

namespace App\Modules\Feed\Interfaces;

use App\Modules\Feed\Requests\FeedRequest;
use App\Modules\Feed\Responses\FeedResponse;

/**
 * Feed application service contract.
 */
interface FeedServiceInterface
{
    public function index(FeedRequest $request): FeedResponse;

    public function view(int $eventId): FeedResponse;

    public function mine(FeedRequest $request, int $userId): FeedResponse;
}
