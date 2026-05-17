<?php

declare(strict_types=1);

namespace App\Common\Responses;

/**
 * Pagination response helper.
 */
final class PaginatedResponse
{
    /**
     * @param list<array<string, mixed>> $items
     * @param int $page
     * @param int $perPage
     * @param int $total
     */
    public function __construct(
        public readonly array $items,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'pagination' => [
                'page' => $this->page,
                'per_page' => $this->perPage,
                'total' => $this->total,
                'pages' => (int) ceil($this->total / max(1, $this->perPage)),
            ],
        ];
    }
}
