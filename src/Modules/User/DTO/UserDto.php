<?php

declare(strict_types=1);

namespace App\Modules\User\DTO;

/**
 * Public user profile DTO.
 */
final class UserDto
{
    /**
     * @param int $id
     * @param string $username
     * @param string|null $avatarUrl
     * @param string|null $bio
     * @param string $status
     * @param string|null $createdAt
     * @param array<string, int> $stats
     */
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly ?string $avatarUrl,
        public readonly ?string $bio,
        public readonly string $status,
        public readonly ?string $createdAt,
        public readonly array $stats = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'avatar_url' => $this->avatarUrl,
            'bio' => $this->bio,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'stats' => $this->stats,
        ];
    }
}
