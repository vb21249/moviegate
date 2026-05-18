<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

/**
 * Password reset completion payload.
 */
final class ResetPasswordRequest extends AuthRequest
{
    public string $token = '';
    public string $newPassword = '';

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['token', 'newPassword'], 'required'],
            ['token', 'string', 'max' => 2048],
            ['newPassword', 'string', 'min' => 8, 'max' => 255],
        ];
    }
}
