<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for playlists.
 *
 * @property int|string $id
 */
final class PlaylistRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%playlists}}';
    }
}