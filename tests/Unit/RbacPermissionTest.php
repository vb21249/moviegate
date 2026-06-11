<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Auth\RbacPermission;
use PHPUnit\Framework\TestCase;

final class RbacPermissionTest extends TestCase
{
    public function testEveryPermissionHasDescription(): void
    {
        $descriptions = RbacPermission::descriptions();
        $knownPermissions = array_unique(array_merge(
            RbacPermission::userPermissions(),
            RbacPermission::adminPermissions()
        ));

        sort($knownPermissions);
        $descriptionKeys = array_keys($descriptions);
        sort($descriptionKeys);

        self::assertSame($knownPermissions, $descriptionKeys);
    }

    public function testIntegrationPermissionsAreAdminOnly(): void
    {
        self::assertNotContains(RbacPermission::INTEGRATION_SYNC, RbacPermission::userPermissions());
        self::assertContains(RbacPermission::INTEGRATION_SYNC, RbacPermission::adminPermissions());
    }

    public function testLoggingPermissionIsAdminOnly(): void
    {
        self::assertNotContains(RbacPermission::LOGGING_ACCESS, RbacPermission::userPermissions());
        self::assertContains(RbacPermission::LOGGING_ACCESS, RbacPermission::adminPermissions());
    }
}
