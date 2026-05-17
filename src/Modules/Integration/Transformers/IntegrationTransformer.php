<?php

declare(strict_types=1);

namespace App\Modules\Integration\Transformers;

use App\Modules\Integration\Responses\IntegrationResponse;

/**
 * Integration transformer placeholder.
 */
final class IntegrationTransformer
{
    /**
     * @param array<string, mixed> $payload
     */
    public function transform(array $payload = []): IntegrationResponse
    {
        return new IntegrationResponse($payload);
    }
}