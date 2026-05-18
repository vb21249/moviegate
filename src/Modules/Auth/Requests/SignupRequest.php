<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

/**
 * Signup request payload.
 */
final class SignupRequest extends AuthRequest
{
    public string $email = '';
    public string $username = '';
    public string $password = '';

    public function rules(): array
    {
        return [
            [['email', 'username', 'password'], 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 190],
            ['username', 'string', 'min' => 3, 'max' => 100],
            ['password', 'string', 'min' => 8, 'max' => 255],
            [['email', 'username'], 'trim'],
        ];
    }
}
