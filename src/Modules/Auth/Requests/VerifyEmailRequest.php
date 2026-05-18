<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

/**
 * Email verification request payload.
 */
final class VerifyEmailRequest extends AuthRequest
{
    public string $token = '';

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['token'], 'required'],
            ['token', 'string', 'max' => 2048],
        ];
    }
}
