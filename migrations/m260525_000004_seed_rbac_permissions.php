<?php

declare(strict_types=1);

use App\Common\Auth\RbacPermission;
use App\Common\Auth\RbacRole;
use yii\db\Migration;
use yii\rbac\Item;
use yii\rbac\ManagerInterface;

/**
 * Seeds roles and permissions used by API route-level RBAC.
 */
final class m260525_000004_seed_rbac_permissions extends Migration
{
    public function safeUp(): void
    {
        $auth = $this->auth();

        foreach (RbacPermission::descriptions() as $name => $description) {
            if ($auth->getPermission($name) !== null) {
                continue;
            }

            $permission = $auth->createPermission($name);
            $permission->description = $description;
            $auth->add($permission);
        }

        $user = $this->ensureRole($auth, RbacRole::USER, 'Regular authenticated user');
        $moderator = $this->ensureRole($auth, RbacRole::MODERATOR, 'Content moderator');
        $admin = $this->ensureRole($auth, RbacRole::ADMIN, 'System administrator');

        foreach (RbacPermission::userPermissions() as $permissionName) {
            $permission = $auth->getPermission($permissionName);

            if ($permission !== null && !$auth->hasChild($user, $permission)) {
                $auth->addChild($user, $permission);
            }
        }

        foreach (RbacPermission::adminPermissions() as $permissionName) {
            $permission = $auth->getPermission($permissionName);

            if ($permission !== null && !$auth->hasChild($admin, $permission)) {
                $auth->addChild($admin, $permission);
            }
        }

        if (!$auth->hasChild($moderator, $user)) {
            $auth->addChild($moderator, $user);
        }

        if (!$auth->hasChild($admin, $moderator)) {
            $auth->addChild($admin, $moderator);
        }
    }

    public function safeDown(): void
    {
        $auth = $this->auth();

        foreach ([RbacRole::ADMIN, RbacRole::MODERATOR, RbacRole::USER] as $roleName) {
            $role = $auth->getRole($roleName);

            if ($role !== null) {
                $auth->remove($role);
            }
        }

        foreach (array_keys(RbacPermission::descriptions()) as $permissionName) {
            $permission = $auth->getPermission($permissionName);

            if ($permission !== null) {
                $auth->remove($permission);
            }
        }
    }

    private function auth(): ManagerInterface
    {
        /** @var ManagerInterface $auth */
        $auth = Yii::$app->authManager;

        return $auth;
    }

    private function ensureRole(ManagerInterface $auth, string $name, string $description): Item
    {
        $role = $auth->getRole($name);

        if ($role !== null) {
            return $role;
        }

        $role = $auth->createRole($name);
        $role->description = $description;
        $auth->add($role);

        return $role;
    }
}
