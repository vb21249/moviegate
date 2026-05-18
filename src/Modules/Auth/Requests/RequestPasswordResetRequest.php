<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

/**
 * Password reset request payload.
 */
final class RequestPasswordResetRequest extends AuthRequest
{
    public string $email = '';

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['email'], 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 190],
            ['email', 'trim'],
        ];
    }
}
