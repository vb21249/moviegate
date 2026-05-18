<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

/**
 * Login request payload.
 */
final class LoginRequest extends AuthRequest
{
    public string $email = '';
    public string $password = '';

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['password', 'string', 'min' => 8, 'max' => 255],
            ['email', 'trim'],
        ];
    }
}
