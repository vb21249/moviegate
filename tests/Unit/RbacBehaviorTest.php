<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Auth\RbacPermission;
use App\Common\Controllers\BaseApiController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use yii\filters\AccessControl;

final class RbacBehaviorTest extends TestCase
{
    public function testBearerAuthWithRbacAddsAccessControlForMappedActions(): void
    {
        $controller = (new ReflectionClass(RbacBehaviorProbeController::class))->newInstanceWithoutConstructor();

        $behaviors = $controller->expose([
            'create' => RbacPermission::REVIEW_CREATE,
        ]);

        self::assertSame(['create'], $behaviors['authenticator']['only']);
        self::assertSame(AccessControl::class, $behaviors['access']['class']);
        self::assertSame(['create'], $behaviors['access']['only']);
        self::assertSame(['@'], $behaviors['access']['rules'][0]['roles']);
    }
}

final class RbacBehaviorProbeController extends BaseApiController
{
    /**
     * @param array<string, string> $permissionsByAction
     *
     * @return array<string, mixed>
     */
    public function expose(array $permissionsByAction): array
    {
        return $this->requireBearerAuthWithRbac($permissionsByAction);
    }
}
