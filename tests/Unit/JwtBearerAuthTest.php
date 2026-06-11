<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Components\JwtBearerAuth;
use App\Common\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class JwtBearerAuthTest extends TestCase
{
    public function testHandleFailureUsesApiExceptionPayloadContract(): void
    {
        $auth = new JwtBearerAuth();

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Authentication is required.');

        try {
            $auth->handleFailure(null);
        } catch (ApiException $exception) {
            self::assertSame(401, $exception->getStatusCode());
            self::assertSame(JwtBearerAuth::CODE_UNAUTHENTICATED, $exception->getErrorCode());

            throw $exception;
        }
    }
}
