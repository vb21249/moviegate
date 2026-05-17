<?php

declare(strict_types=1);

namespace App\Common\Dto;

/**
 * Unified API response DTO.
 */
final class ApiResponseDto
{
    /**
     * @param bool $success
     * @param array<string, mixed>|list<mixed>|null $data
     * @param array<string, mixed>|null $meta
     * @param array<string, mixed>|null $error
     */
    public function __construct(
        public readonly bool $success,
        public readonly array|null $data = null,
        public readonly array|null $meta = null,
        public readonly array|null $error = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'data' => $this->data,
            'meta' => $this->meta,
            'error' => $this->error,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
