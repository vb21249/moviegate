<?php

declare(strict_types=1);

namespace App\Common\Auth;

/**
 * Route-level permissions enforced through Yii RBAC.
 */
final class RbacPermission
{
    public const MOVIE_WATCH = 'movie.watch';
    public const PLAYLIST_READ = 'playlist.read';
    public const PLAYLIST_CREATE = 'playlist.create';
    public const PLAYLIST_UPDATE = 'playlist.update';
    public const PLAYLIST_DELETE = 'playlist.delete';
    public const PLAYLIST_MOVIE_MANAGE = 'playlist.movie.manage';
    public const REVIEW_CREATE = 'review.create';
    public const REVIEW_UPDATE = 'review.update';
    public const REVIEW_DELETE = 'review.delete';
    public const RATING_CREATE = 'rating.create';
    public const RATING_UPDATE = 'rating.update';
    public const RATING_DELETE = 'rating.delete';
    public const RATING_HISTORY = 'rating.history';
    public const COMMENT_CREATE = 'comment.create';
    public const COMMENT_REPLY = 'comment.reply';
    public const COMMENT_UPDATE = 'comment.update';
    public const COMMENT_DELETE = 'comment.delete';
    public const COMMENT_LIKE = 'comment.like';
    public const FEED_MINE = 'feed.mine';
    public const NOTIFICATION_READ = 'notification.read';
    public const NOTIFICATION_UPDATE = 'notification.update';
    public const RECOMMENDATION_REBUILD = 'recommendation.rebuild';
    public const INTEGRATION_ACCESS = 'integration.access';
    public const INTEGRATION_SYNC = 'integration.sync';

    /**
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            self::MOVIE_WATCH => 'Mark movies as watched',
            self::PLAYLIST_READ => 'Read own playlists',
            self::PLAYLIST_CREATE => 'Create playlists',
            self::PLAYLIST_UPDATE => 'Update own playlists',
            self::PLAYLIST_DELETE => 'Delete own playlists',
            self::PLAYLIST_MOVIE_MANAGE => 'Manage movies in own playlists',
            self::REVIEW_CREATE => 'Create reviews',
            self::REVIEW_UPDATE => 'Update own reviews',
            self::REVIEW_DELETE => 'Delete own reviews',
            self::RATING_CREATE => 'Create ratings',
            self::RATING_UPDATE => 'Update own ratings',
            self::RATING_DELETE => 'Delete own ratings',
            self::RATING_HISTORY => 'Read own rating history',
            self::COMMENT_CREATE => 'Create comments',
            self::COMMENT_REPLY => 'Reply to comments',
            self::COMMENT_UPDATE => 'Update own comments',
            self::COMMENT_DELETE => 'Delete own comments',
            self::COMMENT_LIKE => 'Like comments',
            self::FEED_MINE => 'Read own feed activity',
            self::NOTIFICATION_READ => 'Read own notifications',
            self::NOTIFICATION_UPDATE => 'Update own notifications',
            self::RECOMMENDATION_REBUILD => 'Rebuild own recommendations',
            self::INTEGRATION_ACCESS => 'Read integration diagnostics',
            self::INTEGRATION_SYNC => 'Run external integration sync',
        ];
    }

    /**
     * @return list<string>
     */
    public static function userPermissions(): array
    {
        return [
            self::MOVIE_WATCH,
            self::PLAYLIST_READ,
            self::PLAYLIST_CREATE,
            self::PLAYLIST_UPDATE,
            self::PLAYLIST_DELETE,
            self::PLAYLIST_MOVIE_MANAGE,
            self::REVIEW_CREATE,
            self::REVIEW_UPDATE,
            self::REVIEW_DELETE,
            self::RATING_CREATE,
            self::RATING_UPDATE,
            self::RATING_DELETE,
            self::RATING_HISTORY,
            self::COMMENT_CREATE,
            self::COMMENT_REPLY,
            self::COMMENT_UPDATE,
            self::COMMENT_DELETE,
            self::COMMENT_LIKE,
            self::FEED_MINE,
            self::NOTIFICATION_READ,
            self::NOTIFICATION_UPDATE,
            self::RECOMMENDATION_REBUILD,
        ];
    }

    /**
     * @return list<string>
     */
    public static function adminPermissions(): array
    {
        return [
            self::INTEGRATION_ACCESS,
            self::INTEGRATION_SYNC,
        ];
    }
}
