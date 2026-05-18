<?php

declare(strict_types=1);

namespace App\Common\Services;

use App\Common\Contracts\JwtServiceInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ramsey\Uuid\Uuid;
use Yii;

/**
 * JWT adapter based on firebase/php-jwt.
 */
final class FirebaseJwtService implements JwtServiceInterface
{
    const TYPE_ACCESS = 'access';
    const TYPE_REFRESH = 'refresh';

    public function issueAccessToken(array $payload): string
    {
        return $this->issueToken($payload, (int) Yii::$app->params['jwt']['ttl'], self::TYPE_ACCESS);
    }

    public function issueRefreshToken(array $payload): string
    {
        return $this->issueToken($payload, (int) Yii::$app->params['jwt']['refreshTtl'], self::TYPE_REFRESH);
    }

    public function issueToken(array $payload, int $ttl, string $type): string
    {
        $issuedAt = time();
        $payload['iat'] = $issuedAt;
        $payload['exp'] = $issuedAt + $ttl;
        $payload['jti'] = Uuid::uuid7()->toString();
        $payload['type'] = $type;

        return JWT::encode($payload, (string) Yii::$app->params['jwt']['secret'], 'HS256');
    }

    public function parse(string $token): array
    {
        return (array) JWT::decode($token, new Key((string) Yii::$app->params['jwt']['secret'], 'HS256'));
    }
}
