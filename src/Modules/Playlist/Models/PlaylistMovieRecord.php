<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for playlist_movies.
 *
 * @property int|string $id
 * @property int $playlist_id
 * @property int $movie_id
 * @property int $position
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 */
final class PlaylistMovieRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%playlist_movies}}';
    }
}
