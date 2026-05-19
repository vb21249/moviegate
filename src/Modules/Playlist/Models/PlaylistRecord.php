<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for playlists.
 *
 * @property int|string $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $visibility
 * @property bool|int $is_default_watch_later
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 */
final class PlaylistRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%playlists}}';
    }
}
