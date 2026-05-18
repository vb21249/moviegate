<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

/**
 * Refresh-token rotation request payload.
 */
final class RefreshTokenRequest extends AuthRequest
{
    public string $refreshToken = '';

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['refreshToken'], 'required'],
            ['refreshToken', 'string', 'max' => 2048],
        ];
    }

    /**
     * @param array<string, mixed> $bodyParams
     */
    public function loadFromBody(array $bodyParams): bool
    {
        if (isset($bodyParams['refresh_token'])) {
            $this->refreshToken = (string) $bodyParams['refresh_token'];
        }

        return true;
    }
}
