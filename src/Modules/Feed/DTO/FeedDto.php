<?php

declare(strict_types=1);

namespace App\Modules\Feed\DTO;

/**
 * Feed DTO.
 */
final class FeedDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}