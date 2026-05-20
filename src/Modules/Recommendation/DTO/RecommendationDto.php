<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\DTO;

/**
 * Public recommendation item DTO.
 */
final class RecommendationDto
{
    /**
     * @param array<string, mixed> $movie
     * @param array<string, int|float|null> $stats
     */
    public function __construct(
        public readonly int $rank,
        public readonly string $source,
        public readonly string $reason,
        public readonly float $score,
        public readonly array $movie,
        public readonly array $stats = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'rank' => $this->rank,
            'source' => $this->source,
            'reason' => $this->reason,
            'score' => $this->score,
            'movie' => $this->movie,
            'stats' => $this->stats,
        ];
    }
}
