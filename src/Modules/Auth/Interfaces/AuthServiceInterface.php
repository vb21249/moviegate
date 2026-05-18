<?php

declare(strict_types=1);

namespace App\Modules\Auth\Interfaces;

use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\LogoutRequest;
use App\Modules\Auth\Requests\RefreshTokenRequest;
use App\Modules\Auth\Requests\RequestPasswordResetRequest;
use App\Modules\Auth\Requests\ResetPasswordRequest;
use App\Modules\Auth\Requests\SignupRequest;
use App\Modules\Auth\Requests\VerifyEmailRequest;
use App\Modules\Auth\Responses\AuthResponse;

/**
 * Auth application service contract.
 */
interface AuthServiceInterface
{
    /**
     * Registers a user and returns an authenticated session payload.
     *
     * @param SignupRequest $request
     *
     * @return AuthResponse
     */
    public function signup(SignupRequest $request): AuthResponse;

    /**
     * Authenticates a user by email and password.
     *
     * @param LoginRequest $request
     *
     * @return AuthResponse
     */
    public function login(LoginRequest $request): AuthResponse;

    /**
     * Revokes an active refresh-token based session.
     *
     * @param LogoutRequest $request
     *
     * @return AuthResponse
     */
    public function logout(LogoutRequest $request): AuthResponse;

    /**
     * Rotates refresh token and issues a new access token pair.
     *
     * @param RefreshTokenRequest $request
     *
     * @return AuthResponse
     */
    public function refresh(RefreshTokenRequest $request): AuthResponse;

    /**
     * Marks a user email as verified using a signed token.
     *
     * @param VerifyEmailRequest $request
     *
     * @return AuthResponse
     */
    public function verifyEmail(VerifyEmailRequest $request): AuthResponse;

    /**
     * Creates a password reset token for a known email.
     *
     * @param RequestPasswordResetRequest $request
     *
     * @return AuthResponse
     */
    public function requestPasswordReset(RequestPasswordResetRequest $request): AuthResponse;

    /**
     * Resets password using a signed reset token.
     *
     * @param ResetPasswordRequest $request
     *
     * @return AuthResponse
     */
    public function resetPassword(ResetPasswordRequest $request): AuthResponse;
}
