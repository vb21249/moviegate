<?php

declare(strict_types=1);

namespace App\Modules\Comment\Transformers;

use App\Modules\Comment\Responses\CommentResponse;

/**
 * Comment transformer placeholder.
 */
final class CommentTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): CommentResponse
    {
        return new CommentResponse($payload);
    }
}