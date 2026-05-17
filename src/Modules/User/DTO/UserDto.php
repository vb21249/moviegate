<?php

declare(strict_types=1);

namespace App\Modules\User\DTO;

/**
 * User DTO.
 */
final class UserDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}