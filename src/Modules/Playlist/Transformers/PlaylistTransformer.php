<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Transformers;

use App\Modules\Playlist\Responses\PlaylistResponse;

/**
 * Playlist transformer placeholder.
 */
final class PlaylistTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): PlaylistResponse
    {
        return new PlaylistResponse($payload);
    }
}