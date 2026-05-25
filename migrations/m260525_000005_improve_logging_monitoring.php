<?php

declare(strict_types=1);

use App\Common\Auth\RbacPermission;
use App\Common\Auth\RbacRole;
use yii\db\Migration;
use yii\rbac\ManagerInterface;

/**
 * Adds API log monitoring indexes and admin permission for log diagnostics.
 */
final class m260525_000005_improve_logging_monitoring extends Migration
{
    public function safeUp(): void
    {
        $this->createIndex('idx_api_logs_created_at', '{{%api_logs}}', 'created_at');
        $this->createIndex('idx_api_logs_status_created', '{{%api_logs}}', ['response_status', 'created_at']);
        $this->createIndex('idx_integration_logs_created_at', '{{%integration_logs}}', 'created_at');

        $this->ensureLoggingPermission();
    }

    public function safeDown(): void
    {
        $this->removeLoggingPermission();

        $this->dropIndex('idx_integration_logs_created_at', '{{%integration_logs}}');
        $this->dropIndex('idx_api_logs_status_created', '{{%api_logs}}');
        $this->dropIndex('idx_api_logs_created_at', '{{%api_logs}}');
    }

    private function ensureLoggingPermission(): void
    {
        $auth = $this->auth();
        $permission = $auth->getPermission(RbacPermission::LOGGING_ACCESS);

        if ($permission === null) {
            $permission = $auth->createPermission(RbacPermission::LOGGING_ACCESS);
            $permission->description = RbacPermission::descriptions()[RbacPermission::LOGGING_ACCESS];
            $auth->add($permission);
        }

        $admin = $auth->getRole(RbacRole::ADMIN);

        if ($admin !== null && !$auth->hasChild($admin, $permission)) {
            $auth->addChild($admin, $permission);
        }
    }

    private function removeLoggingPermission(): void
    {
        $auth = $this->auth();
        $permission = $auth->getPermission(RbacPermission::LOGGING_ACCESS);

        if ($permission !== null) {
            $auth->remove($permission);
        }
    }

    private function auth(): ManagerInterface
    {
        /** @var ManagerInterface $auth */
        $auth = Yii::$app->authManager;

        return $auth;
    }
}
