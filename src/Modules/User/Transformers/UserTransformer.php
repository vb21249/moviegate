<?php

declare(strict_types=1);

namespace App\Modules\User\Transformers;

use App\Modules\User\Responses\UserResponse;

/**
 * User transformer placeholder.
 */
final class UserTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): UserResponse
    {
        return new UserResponse($payload);
    }
}