<?php

declare(strict_types=1);

namespace App\Common\Components;

use App\Common\Exceptions\ApiException;
use yii\filters\auth\HttpBearerAuth;

/**
 * Bearer token authenticator that keeps API errors in the project response format.
 */
final class JwtBearerAuth extends HttpBearerAuth
{
    public const CODE_UNAUTHENTICATED = 'unauthenticated';

    /**
     * @param mixed $response
     */
    public function handleFailure($response): void
    {
        throw new ApiException(
            'Authentication is required.',
            401,
            self::CODE_UNAUTHENTICATED
        );
    }
}
