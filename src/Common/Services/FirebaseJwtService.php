<?php

declare(strict_types=1);

namespace App\Common\Services;

use App\Common\Contracts\JwtServiceInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Yii;

/**
 * JWT adapter based on firebase/php-jwt.
 */
final class FirebaseJwtService implements JwtServiceInterface
{
    public function issueAccessToken(array $payload): string
    {
        $payload['exp'] = time() + (int) Yii::$app->params['jwt']['ttl'];

        return JWT::encode($payload, (string) Yii::$app->params['jwt']['secret'], 'HS256');
    }

    public function issueRefreshToken(array $payload): string
    {
        $payload['exp'] = time() + (int) Yii::$app->params['jwt']['refreshTtl'];
        $payload['type'] = 'refresh';

        return JWT::encode($payload, (string) Yii::$app->params['jwt']['secret'], 'HS256');
    }

    public function parse(string $token): array
    {
        return (array) JWT::decode($token, new Key((string) Yii::$app->params['jwt']['secret'], 'HS256'));
    }
}
