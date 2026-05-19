<?php

declare(strict_types=1);

namespace App\Modules\Comment\Transformers;

/**
 * Builds comment-specific API payload fragments.
 */
final class CommentTransformer
{
    /**
     * @return array<string, int|bool>
     */
    public function deletedPayload(int $commentId): array
    {
        return [
            'deleted' => true,
            'comment_id' => $commentId,
        ];
    }

    /**
     * @return array<string, int|bool>
     */
    public function likedPayload(int $commentId, int $likesCount): array
    {
        return [
            'liked' => true,
            'comment_id' => $commentId,
            'likes_count' => $likesCount,
        ];
    }
}
