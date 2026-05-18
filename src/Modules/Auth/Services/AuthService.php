<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Common\Contracts\JwtServiceInterface;
use App\Common\Services\AbstractService;
use App\Modules\Auth\DTO\AuthActionTokenDto;
use App\Modules\Auth\DTO\AuthSessionDataDto;
use App\Modules\Auth\DTO\AuthTokenPairDto;
use App\Modules\Auth\DTO\AuthUserDto;
use App\Modules\Auth\DTO\CreateUserDto;
use App\Modules\Auth\Enums\AuthActionType;
use App\Modules\Auth\Exceptions\AuthException;
use App\Modules\Auth\Interfaces\AuthRepositoryInterface;
use App\Modules\Auth\Interfaces\AuthServiceInterface;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\LogoutRequest;
use App\Modules\Auth\Requests\RefreshTokenRequest;
use App\Modules\Auth\Requests\RequestPasswordResetRequest;
use App\Modules\Auth\Requests\ResetPasswordRequest;
use App\Modules\Auth\Requests\SignupRequest;
use App\Modules\Auth\Requests\VerifyEmailRequest;
use App\Modules\Auth\Responses\AuthResponse;
use App\Modules\User\Models\UserRecord;
use Throwable;
use Yii;

/**
 * Working auth service for local API usage and Postman verification.
 */
final class AuthService extends AbstractService implements AuthServiceInterface
{
    public function __construct(
        private readonly AuthRepositoryInterface $repository,
        private readonly JwtServiceInterface $jwtService,
    ) {
    }

    public function signup(SignupRequest $request): AuthResponse
    {
        if ($this->repository->findUserByEmail($request->email) !== null) {
            throw new AuthException(
                'User with such email already exists',
                409,
                AuthException::CODE_EMAIL_ALREADY_EXISTS
            );
        }

        if ($this->repository->findUserByUsername($request->username) !== null) {
            throw new AuthException(
                'User with such username already exists',
                409,
                AuthException::CODE_USERNAME_ALREADY_EXISTS
            );
        }

        $user = $this->repository->createUser(new CreateUserDto(
            email: mb_strtolower(trim($request->email)),
            username: trim($request->username),
            passwordHash: Yii::$app->security->generatePasswordHash($request->password),
        ));

        $tokens = $this->issueTokenPair($user);
        $verificationToken = $this->buildActionToken(
            userId: (int) $user->id,
            type: AuthActionType::EmailVerification,
            ttl: (int) Yii::$app->params['jwt']['emailVerificationTtl']
        );

        $this->persistSession($user, $tokens);
        $this->repository->updateLastLoginAt((int) $user->id);

        return new AuthResponse(
            message: 'Registration was successful. The user has been automatically logged in',
            user: $this->mapUser($this->repository->findUserById((int) $user->id) ?? $user),
            tokens: $tokens,
            actionToken: $verificationToken,
        );
    }

    public function login(LoginRequest $request): AuthResponse
    {
        $user = $this->repository->findUserByEmail(mb_strtolower(trim($request->email)));

        if ($user === null || !Yii::$app->security->validatePassword($request->password, (string) $user->password_hash)) {
            throw new AuthException(
                'Incorrect email or password',
                401,
                AuthException::CODE_INVALID_CREDENTIALS
            );
        }

        $tokens = $this->issueTokenPair($user);
        $this->persistSession($user, $tokens);
        $this->repository->updateLastLoginAt((int) $user->id);

        return new AuthResponse(
            message: 'Login successful',
            user: $this->mapUser($this->repository->findUserById((int) $user->id) ?? $user),
            tokens: $tokens,
        );
    }

    public function logout(LogoutRequest $request): AuthResponse
    {
        $session = $this->repository->findActiveSessionByRefreshToken($request->refreshToken);

        if ($session === null) {
            throw new AuthException(
                'No active session was found for this refresh token',
                404,
                AuthException::CODE_SESSION_NOT_FOUND
            );
        }

        $this->repository->revokeSessionByRefreshToken($request->refreshToken);

        return new AuthResponse(
            message: 'Logout successful'
        );
    }

    public function refresh(RefreshTokenRequest $request): AuthResponse
    {
        $payload = $this->parseToken($request->refreshToken, 'refresh');
        $session = $this->repository->findActiveSessionByRefreshToken($request->refreshToken);

        if ($session === null) {
            throw new AuthException(
                'The refresh token is invalid or has already been revoked',
                401,
                AuthException::CODE_INVALID_REFRESH_TOKEN
            );
        }

        $userId = (int) ($payload['sub'] ?? 0);
        $user = $this->repository->findUserById($userId);

        if ($user === null) {
            throw new AuthException('User not found', 404, AuthException::CODE_USER_NOT_FOUND);
        }

        $this->repository->revokeSessionByRefreshToken($request->refreshToken);

        $tokens = $this->issueTokenPair($user);
        $this->persistSession($user, $tokens);

        return new AuthResponse(
            message: 'The tokens have been successfully updated',
            user: $this->mapUser($user),
            tokens: $tokens,
        );
    }

    public function verifyEmail(VerifyEmailRequest $request): AuthResponse
    {
        $payload = $this->parseToken($request->token, AuthActionType::EmailVerification->value);
        $userId = (int) ($payload['sub'] ?? 0);
        $user = $this->repository->findUserById($userId);

        if ($user === null) {
            throw new AuthException('User not found', 404, AuthException::CODE_USER_NOT_FOUND);
        }

        $this->repository->markEmailVerified($userId);
        $freshUser = $this->repository->findUserById($userId) ?? $user;

        return new AuthResponse(
            message: 'Email confirmed',
            user: $this->mapUser($freshUser),
        );
    }

    public function requestPasswordReset(RequestPasswordResetRequest $request): AuthResponse
    {
        $user = $this->repository->findUserByEmail(mb_strtolower(trim($request->email)));

        if ($user === null) {
            return new AuthResponse(
                message: 'If a user with that email address exists, the reset token has already been generated'
            );
        }

        return new AuthResponse(
            message: 'A password reset token has been generated. It is returned in the API response for local verification',
            user: $this->mapUser($user),
            actionToken: $this->buildActionToken(
                userId: (int) $user->id,
                type: AuthActionType::PasswordReset,
                ttl: (int) Yii::$app->params['jwt']['passwordResetTtl']
            ),
        );
    }

    public function resetPassword(ResetPasswordRequest $request): AuthResponse
    {
        $payload = $this->parseToken($request->token, AuthActionType::PasswordReset->value);
        $userId = (int) ($payload['sub'] ?? 0);
        $user = $this->repository->findUserById($userId);

        if ($user === null) {
            throw new AuthException('User not found', 404, AuthException::CODE_USER_NOT_FOUND);
        }

        $this->repository->updatePasswordHash(
            $userId,
            Yii::$app->security->generatePasswordHash($request->newPassword)
        );
        $this->repository->revokeAllActiveSessionsForUser($userId);

        return new AuthResponse(
            message: 'Password has been successfully changed. All active sessions have been terminated',
            user: $this->mapUser($this->repository->findUserById($userId) ?? $user),
        );
    }

    /**
     * @param UserRecord $user
     *
     * @return AuthUserDto
     */
    private function mapUser(UserRecord $user): AuthUserDto
    {
        return new AuthUserDto(
            id: (int) $user->id,
            email: (string) $user->email,
            username: (string) $user->username,
            emailVerified: $user->email_verified_at !== null,
            avatarUrl: $user->avatar_url !== null ? (string) $user->avatar_url : null,
            bio: $user->bio !== null ? (string) $user->bio : null,
            lastLoginAt: $user->last_login_at !== null ? (string) $user->last_login_at : null,
        );
    }

    /**
     * @param UserRecord $user
     *
     * @return AuthTokenPairDto
     */
    private function issueTokenPair(UserRecord $user): AuthTokenPairDto
    {
        $accessToken = $this->jwtService->issueAccessToken([
            'sub' => (int) $user->id,
            'email' => (string) $user->email,
            'username' => (string) $user->username,
        ]);
        $refreshToken = $this->jwtService->issueRefreshToken([
            'sub' => (int) $user->id,
        ]);

        return new AuthTokenPairDto(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            tokenType: 'Bearer',
            expiresIn: (int) Yii::$app->params['jwt']['ttl'],
            refreshExpiresIn: (int) Yii::$app->params['jwt']['refreshTtl'],
        );
    }

    /**
     * @param UserRecord $user
     * @param AuthTokenPairDto $tokens
     */
    private function persistSession(UserRecord $user, AuthTokenPairDto $tokens): void
    {
        $request = Yii::$app->request;

        $this->repository->createSession(new AuthSessionDataDto(
            userId: (int) $user->id,
            refreshToken: $tokens->refreshToken,
            ipAddress: $request->userIP,
            userAgent: $request->userAgent !== null ? mb_substr($request->userAgent, 0, 255) : null,
            expiresAt: gmdate('Y-m-d H:i:s', time() + (int) Yii::$app->params['jwt']['refreshTtl']),
        ));
    }

    /**
     * @param int $userId
     * @param AuthActionType $type
     * @param int $ttl
     *
     * @return AuthActionTokenDto
     */
    private function buildActionToken(int $userId, AuthActionType $type, int $ttl): AuthActionTokenDto
    {
        $type = $type->value;
        return new AuthActionTokenDto(
            token: $this->jwtService->issueToken(['sub' => $userId], $ttl, $type),
            type: $type,
            expiresIn: $ttl,
        );
    }

    /**
     * @param string $token
     * @param string $expectedType
     *
     * @return array<string, mixed>
     */
    private function parseToken(string $token, string $expectedType): array
    {
        try {
            $payload = $this->jwtService->parse($token);
        } catch (Throwable $exception) {
            throw new AuthException(
                'The token is invalid or has expired',
                401,
                AuthException::CODE_INVALID_TOKEN
            );
        }

        if (($payload['type'] ?? null) !== $expectedType) {
            throw new AuthException(
                'An invalid token type was passed',
                401,
                AuthException::CODE_INVALID_TOKEN_TYPE
            );
        }

        return $payload;
    }
}
