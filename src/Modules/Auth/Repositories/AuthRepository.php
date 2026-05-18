<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Common\Repositories\BaseRepository;
use App\Modules\Auth\DTO\AuthSessionDataDto;
use App\Modules\Auth\DTO\CreateUserDto;
use App\Modules\Auth\Exceptions\AuthException;
use App\Modules\Auth\Interfaces\AuthRepositoryInterface;
use App\Modules\Auth\Models\AuthRecord;
use App\Modules\User\Models\UserRecord;
use yii\db\Expression;

/**
 * Auth repository backed by users and user_sessions tables.
 */
final class AuthRepository extends BaseRepository implements AuthRepositoryInterface
{
    public function findUserByEmail(string $email): ?UserRecord
    {
        /** @var UserRecord|null $user */
        $user = UserRecord::find()->andWhere(['email' => $email])->one();

        return $user;
    }

    public function findUserByUsername(string $username): ?UserRecord
    {
        /** @var UserRecord|null $user */
        $user = UserRecord::find()->andWhere(['username' => $username])->one();

        return $user;
    }

    public function findUserById(int $userId): ?UserRecord
    {
        /** @var UserRecord|null $user */
        $user = UserRecord::findOne($userId);

        return $user;
    }

    public function createUser(CreateUserDto $dto): UserRecord
    {
        $user = new UserRecord();
        $user->email = $dto->email;
        $user->username = $dto->username;
        $user->password_hash = $dto->passwordHash;
        $user->status = 'active';

        if (!$user->save()) {
            throw new AuthException(
                'Failed to save a user',
                500,
                AuthException::CODE_USER_CREATE_FAILED
            );
        }

        return $user;
    }

    public function createSession(AuthSessionDataDto $dto): AuthRecord
    {
        $session = new AuthRecord();
        $session->user_id = $dto->userId;
        $session->refresh_token = $dto->refreshToken;
        $session->ip_address = $dto->ipAddress;
        $session->user_agent = $dto->userAgent;
        $session->expires_at = $dto->expiresAt;

        if (!$session->save()) {
            throw new AuthException(
                'Failed to save a user session',
                500,
                AuthException::CODE_SESSION_CREATE_FAILED
            );
        }

        return $session;
    }

    public function findActiveSessionByRefreshToken(string $refreshToken): ?AuthRecord
    {
        /** @var AuthRecord|null $session */
        $session = AuthRecord::find()
            ->andWhere(['refresh_token' => $refreshToken])
            ->andWhere(['revoked_at' => null])
            ->andWhere(['>', 'expires_at', new Expression('NOW()')])
            ->one();

        return $session;
    }

    public function revokeSessionByRefreshToken(string $refreshToken): void
    {
        AuthRecord::updateAll(
            ['revoked_at' => new Expression('NOW()')],
            ['refresh_token' => $refreshToken]
        );
    }

    public function revokeAllActiveSessionsForUser(int $userId): void
    {
        AuthRecord::updateAll(
            ['revoked_at' => new Expression('NOW()')],
            ['and', ['user_id' => $userId], ['revoked_at' => null]]
        );
    }

    public function updateLastLoginAt(int $userId): void
    {
        UserRecord::updateAll(
            [
                'last_login_at' => new Expression('NOW()'),
                'updated_at' => new Expression('NOW()'),
            ],
            ['id' => $userId]
        );
    }

    public function markEmailVerified(int $userId): void
    {
        UserRecord::updateAll(
            [
                'email_verified_at' => new Expression('NOW()'),
                'updated_at' => new Expression('NOW()'),
            ],
            ['id' => $userId]
        );
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        UserRecord::updateAll(
            [
                'password_hash' => $passwordHash,
                'updated_at' => new Expression('NOW()'),
            ],
            ['id' => $userId]
        );
    }
}
