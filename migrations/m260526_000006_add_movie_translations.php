<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds localized movie catalog fields.
 */
final class m260526_000006_add_movie_translations extends Migration
{
    private string $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->createTable('{{%movie_translations}}', [
            'id' => $this->primaryKey()->unsigned(),
            'movie_id' => $this->integer()->unsigned()->notNull(),
            'language' => $this->string(16)->notNull(),
            'title' => $this->string(255)->notNull(),
            'original_title' => $this->string(255)->null(),
            'overview' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->tableOptions);

        $this->createIndex('ux_movie_translations_movie_language', '{{%movie_translations}}', ['movie_id', 'language'], true);
        $this->createIndex('idx_movie_translations_language', '{{%movie_translations}}', 'language');
        $this->addForeignKey(
            'fk_movie_translations_movie',
            '{{%movie_translations}}',
            'movie_id',
            '{{%movies}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%movie_translations}}');
    }
}
