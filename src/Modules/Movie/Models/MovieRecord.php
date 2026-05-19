<?php

declare(strict_types=1);

namespace App\Modules\Movie\Models;

use App\Common\Models\BaseActiveRecord;

/**
 * ActiveRecord for movies.
 *
 * @property int|string $id
 * @property int|null $tmdb_id
 * @property string $slug
 * @property string $title
 * @property string|null $original_title
 * @property string|null $overview
 * @property string|null $poster_url
 * @property string|null $backdrop_url
 * @property string|null $release_date
 * @property int|null $runtime_minutes
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 */
final class MovieRecord extends BaseActiveRecord
{
    public static function tableName(): string
    {
        return '{{%movies}}';
    }
}
