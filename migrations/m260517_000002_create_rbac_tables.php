<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Creates RBAC schema required by yii\rbac\DbManager.
 */
final class m260517_000002_create_rbac_tables extends Migration
{
    private string $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->createTable('{{%auth_rule}}', [
            'name' => $this->string(64)->notNull(),
            'data' => $this->binary()->null(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'PRIMARY KEY([[name]])',
        ], $this->tableOptions);

        $this->createTable('{{%auth_item}}', [
            'name' => $this->string(64)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'description' => $this->text()->null(),
            'rule_name' => $this->string(64)->null(),
            'data' => $this->binary()->null(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
            'PRIMARY KEY([[name]])',
        ], $this->tableOptions);

        $this->createTable('{{%auth_item_child}}', [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY([[parent]], [[child]])',
        ], $this->tableOptions);

        $this->createTable('{{%auth_assignment}}', [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->string(64)->notNull(),
            'created_at' => $this->integer()->null(),
            'PRIMARY KEY([[item_name]], [[user_id]])',
        ], $this->tableOptions);

        $this->addForeignKey('fk_auth_item_rule_name', '{{%auth_item}}', 'rule_name', '{{%auth_rule}}', 'name', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_auth_item_child_parent', '{{%auth_item_child}}', 'parent', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_auth_item_child_child', '{{%auth_item_child}}', 'child', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_auth_assignment_item_name', '{{%auth_assignment}}', 'item_name', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%auth_assignment}}');
        $this->dropTable('{{%auth_item_child}}');
        $this->dropTable('{{%auth_item}}');
        $this->dropTable('{{%auth_rule}}');
    }
}
