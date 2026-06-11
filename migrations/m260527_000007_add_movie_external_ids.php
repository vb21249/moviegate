<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Adds provider-neutral external movie identifiers.
 */
final class m260527_000007_add_movie_external_ids extends Migration
{
    private string $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->createTable('{{%movie_external_ids}}', [
            'id' => $this->primaryKey()->unsigned(),
            'movie_id' => $this->integer()->unsigned()->notNull(),
            'provider' => $this->string(50)->notNull(),
            'external_id' => $this->string(190)->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->tableOptions);

        $this->createIndex(
            'ux_movie_external_ids_provider_external',
            '{{%movie_external_ids}}',
            ['provider', 'external_id'],
            true
        );
        $this->createIndex('idx_movie_external_ids_movie', '{{%movie_external_ids}}', 'movie_id');
        $this->addForeignKey(
            'fk_movie_external_ids_movie',
            '{{%movie_external_ids}}',
            'movie_id',
            '{{%movies}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->execute(
            "INSERT INTO {{%movie_external_ids}} (movie_id, provider, external_id, created_at, updated_at)
             SELECT id, 'tmdb', CAST(tmdb_id AS CHAR), NOW(), NOW()
             FROM {{%movies}}
             WHERE tmdb_id IS NOT NULL"
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%movie_external_ids}}');
    }
}
