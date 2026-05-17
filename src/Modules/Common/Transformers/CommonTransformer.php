<?php

declare(strict_types=1);

namespace App\Modules\Common\Transformers;

use App\Modules\Common\Responses\CommonResponse;

/**
 * Common transformer placeholder.
 */
final class CommonTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): CommonResponse
    {
        return new CommonResponse($payload);
    }
}