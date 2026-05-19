<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Aligns movies.status with the current MovieStatus enum.
 */
final class m260519_000003_fix_movies_status_default extends Migration
{
    private const TABLE = '{{%movies}}';
    private const STATUS_PUBLISHED = 'published';
    private const STATUS_ACTIVE = 'active';

    public function safeUp(): void
    {
        $this->update(
            self::TABLE,
            ['status' => self::STATUS_ACTIVE],
            ['status' => self::STATUS_PUBLISHED]
        );

        $this->alterColumn(
            self::TABLE,
            'status',
            $this->string(32)->notNull()->defaultValue(self::STATUS_ACTIVE)
        );
    }

    public function safeDown(): void
    {
        $this->alterColumn(
            self::TABLE,
            'status',
            $this->string(32)->notNull()->defaultValue(self::STATUS_PUBLISHED)
        );

        $this->update(
            self::TABLE,
            ['status' => self::STATUS_PUBLISHED],
            ['status' => self::STATUS_ACTIVE]
        );
    }
}
