<?php

declare(strict_types=1);

namespace App\Modules\Auth\Interfaces;

use App\Modules\Auth\DTO\AuthSessionDataDto;
use App\Modules\Auth\DTO\CreateUserDto;
use App\Modules\Auth\Models\AuthRecord;
use App\Modules\User\Models\UserRecord;

/**
 * Persistence contract for auth-related state.
 */
interface AuthRepositoryInterface
{
    /**
     * @param string $email
     *
     * @return UserRecord|null
     */
    public function findUserByEmail(string $email): ?UserRecord;

    /**
     * @param string $username
     *
     * @return UserRecord|null
     */
    public function findUserByUsername(string $username): ?UserRecord;

    /**
     * @param int $userId
     *
     * @return UserRecord|null
     */
    public function findUserById(int $userId): ?UserRecord;

    /**
     * @param CreateUserDto $dto
     *
     * @return UserRecord
     */
    public function createUser(CreateUserDto $dto): UserRecord;

    /**
     * @param AuthSessionDataDto $dto
     *
     * @return AuthRecord
     */
    public function createSession(AuthSessionDataDto $dto): AuthRecord;

    /**
     * @param string $refreshToken
     *
     * @return AuthRecord|null
     */
    public function findActiveSessionByRefreshToken(string $refreshToken): ?AuthRecord;

    /**
     * @param string $refreshToken
     */
    public function revokeSessionByRefreshToken(string $refreshToken): void;

    /**
     * @param int $userId
     */
    public function revokeAllActiveSessionsForUser(int $userId): void;

    /**
     * @param int $userId
     */
    public function updateLastLoginAt(int $userId): void;

    /**
     * @param int $userId
     */
    public function markEmailVerified(int $userId): void;

    /**
     * @param int $userId
     * @param string $passwordHash
     */
    public function updatePasswordHash(int $userId, string $passwordHash): void;
}
