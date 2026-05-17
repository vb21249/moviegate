<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Creates the core domain schema.
 */
final class m260517_000001_create_core_tables extends Migration
{
    private string $tableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey()->unsigned(),
            'email' => $this->string(190)->notNull()->unique(),
            'username' => $this->string(100)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'avatar_url' => $this->string(255)->null(),
            'bio' => $this->text()->null(),
            'status' => $this->string(32)->notNull()->defaultValue('active'),
            'email_verified_at' => $this->dateTime()->null(),
            'last_login_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
            'deleted_by' => $this->integer()->unsigned()->null(),
        ], $this->tableOptions);

        $this->createTable('{{%movies}}', [
            'id' => $this->primaryKey()->unsigned(),
            'tmdb_id' => $this->integer()->unsigned()->null()->unique(),
            'slug' => $this->string(190)->notNull()->unique(),
            'title' => $this->string(255)->notNull(),
            'original_title' => $this->string(255)->null(),
            'overview' => $this->text()->null(),
            'poster_url' => $this->string(255)->null(),
            'backdrop_url' => $this->string(255)->null(),
            'release_date' => $this->date()->null(),
            'runtime_minutes' => $this->integer()->unsigned()->null(),
            'status' => $this->string(32)->notNull()->defaultValue('published'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
            'deleted_by' => $this->integer()->unsigned()->null(),
        ], $this->tableOptions);

        $this->createTable('{{%playlists}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string(255)->notNull(),
            'slug' => $this->string(190)->notNull(),
            'description' => $this->text()->null(),
            'visibility' => $this->string(32)->notNull()->defaultValue('private'),
            'is_default_watch_later' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
            'deleted_by' => $this->integer()->unsigned()->null(),
        ], $this->tableOptions);
        $this->createIndex('ux_playlists_user_slug', '{{%playlists}}', ['user_id', 'slug'], true);

        $this->createTable('{{%playlist_movies}}', [
            'id' => $this->primaryKey()->unsigned(),
            'playlist_id' => $this->integer()->unsigned()->notNull(),
            'movie_id' => $this->integer()->unsigned()->notNull(),
            'position' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
            'deleted_by' => $this->integer()->unsigned()->null(),
        ], $this->tableOptions);
        $this->createIndex('ux_playlist_movies_playlist_movie', '{{%playlist_movies}}', ['playlist_id', 'movie_id'], true);

        $this->createTable('{{%ratings}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'movie_id' => $this->integer()->unsigned()->notNull(),
            'score' => $this->tinyInteger()->unsigned()->notNull(),
            'review_text' => $this->text()->null(),
            'rated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
            'deleted_by' => $this->integer()->unsigned()->null(),
        ], $this->tableOptions);
        $this->createIndex('idx_ratings_user_movie', '{{%ratings}}', ['user_id', 'movie_id']);

        $this->createTable('{{%reviews}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'movie_id' => $this->integer()->unsigned()->notNull(),
            'rating_id' => $this->integer()->unsigned()->null(),
            'title' => $this->string(255)->notNull(),
            'body' => $this->text()->notNull(),
            'likes_count' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'status' => $this->string(32)->notNull()->defaultValue('published'),
            'published_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
            'deleted_by' => $this->integer()->unsigned()->null(),
        ], $this->tableOptions);

        $this->createTable('{{%comments}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'movie_id' => $this->integer()->unsigned()->null(),
            'review_id' => $this->integer()->unsigned()->null(),
            'parent_id' => $this->integer()->unsigned()->null(),
            'body' => $this->text()->notNull(),
            'likes_count' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->unsigned()->null(),
            'updated_by' => $this->integer()->unsigned()->null(),
            'deleted_by' => $this->integer()->unsigned()->null(),
        ], $this->tableOptions);
        $this->createIndex('idx_comments_parent_id', '{{%comments}}', 'parent_id');

        $this->createTable('{{%comment_likes}}', [
            'id' => $this->primaryKey()->unsigned(),
            'comment_id' => $this->integer()->unsigned()->notNull(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $this->tableOptions);
        $this->createIndex('ux_comment_likes_comment_user', '{{%comment_likes}}', ['comment_id', 'user_id'], true);

        $this->createTable('{{%movie_views}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'movie_id' => $this->integer()->unsigned()->notNull(),
            'viewed_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $this->tableOptions);
        $this->createIndex('ux_movie_views_user_movie', '{{%movie_views}}', ['user_id', 'movie_id'], true);

        $this->createTable('{{%follows}}', [
            'id' => $this->primaryKey()->unsigned(),
            'follower_id' => $this->integer()->unsigned()->notNull(),
            'followed_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $this->tableOptions);
        $this->createIndex('ux_follows_unique', '{{%follows}}', ['follower_id', 'followed_id'], true);

        $this->createTable('{{%feed_events}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'event_type' => $this->string(100)->notNull(),
            'entity_type' => $this->string(100)->notNull(),
            'entity_id' => $this->integer()->unsigned()->null(),
            'payload_json' => $this->json()->null(),
            'occurred_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->tableOptions);
        $this->createIndex('idx_feed_events_user_occurred', '{{%feed_events}}', ['user_id', 'occurred_at']);

        $this->createTable('{{%recommendation_cache}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'source' => $this->string(100)->notNull(),
            'payload_json' => $this->json()->notNull(),
            'expires_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->tableOptions);
        $this->createIndex('idx_recommendation_cache_user_source', '{{%recommendation_cache}}', ['user_id', 'source']);

        $this->createTable('{{%notifications}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'type' => $this->string(100)->notNull(),
            'payload_json' => $this->json()->notNull(),
            'read_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->tableOptions);
        $this->createIndex('idx_notifications_user_read', '{{%notifications}}', ['user_id', 'read_at']);

        $this->createTable('{{%user_sessions}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'refresh_token' => $this->string(255)->notNull()->unique(),
            'ip_address' => $this->string(64)->null(),
            'user_agent' => $this->string(255)->null(),
            'expires_at' => $this->dateTime()->notNull(),
            'revoked_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->tableOptions);
        $this->createIndex('idx_user_sessions_user_expires', '{{%user_sessions}}', ['user_id', 'expires_at']);

        $this->createTable('{{%api_logs}}', [
            'id' => $this->primaryKey()->unsigned(),
            'correlation_id' => $this->string(64)->notNull(),
            'request_method' => $this->string(16)->notNull(),
            'request_uri' => $this->string(255)->notNull(),
            'request_body' => $this->json()->null(),
            'response_status' => $this->integer()->unsigned()->notNull(),
            'response_body' => $this->json()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $this->tableOptions);
        $this->createIndex('idx_api_logs_correlation_id', '{{%api_logs}}', 'correlation_id');

        $this->createTable('{{%integration_logs}}', [
            'id' => $this->primaryKey()->unsigned(),
            'correlation_id' => $this->string(64)->notNull(),
            'service' => $this->string(100)->notNull(),
            'operation' => $this->string(100)->notNull(),
            'request_payload' => $this->json()->null(),
            'response_payload' => $this->json()->null(),
            'status' => $this->string(32)->notNull()->defaultValue('success'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $this->tableOptions);
        $this->createIndex('idx_integration_logs_service_created', '{{%integration_logs}}', ['service', 'created_at']);

        $this->addForeignKey('fk_playlists_user', '{{%playlists}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_playlist_movies_playlist', '{{%playlist_movies}}', 'playlist_id', '{{%playlists}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_playlist_movies_movie', '{{%playlist_movies}}', 'movie_id', '{{%movies}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_ratings_user', '{{%ratings}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_ratings_movie', '{{%ratings}}', 'movie_id', '{{%movies}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_reviews_user', '{{%reviews}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_reviews_movie', '{{%reviews}}', 'movie_id', '{{%movies}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_reviews_rating', '{{%reviews}}', 'rating_id', '{{%ratings}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_comments_user', '{{%comments}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_comments_movie', '{{%comments}}', 'movie_id', '{{%movies}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_comments_review', '{{%comments}}', 'review_id', '{{%reviews}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_comments_parent', '{{%comments}}', 'parent_id', '{{%comments}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_comment_likes_comment', '{{%comment_likes}}', 'comment_id', '{{%comments}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_comment_likes_user', '{{%comment_likes}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_movie_views_user', '{{%movie_views}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_movie_views_movie', '{{%movie_views}}', 'movie_id', '{{%movies}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_follows_follower', '{{%follows}}', 'follower_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_follows_followed', '{{%follows}}', 'followed_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_feed_events_user', '{{%feed_events}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_recommendation_cache_user', '{{%recommendation_cache}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_notifications_user', '{{%notifications}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_user_sessions_user', '{{%user_sessions}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%integration_logs}}');
        $this->dropTable('{{%api_logs}}');
        $this->dropTable('{{%user_sessions}}');
        $this->dropTable('{{%notifications}}');
        $this->dropTable('{{%recommendation_cache}}');
        $this->dropTable('{{%feed_events}}');
        $this->dropTable('{{%follows}}');
        $this->dropTable('{{%movie_views}}');
        $this->dropTable('{{%comment_likes}}');
        $this->dropTable('{{%comments}}');
        $this->dropTable('{{%reviews}}');
        $this->dropTable('{{%ratings}}');
        $this->dropTable('{{%playlist_movies}}');
        $this->dropTable('{{%playlists}}');
        $this->dropTable('{{%movies}}');
        $this->dropTable('{{%users}}');
    }
}
