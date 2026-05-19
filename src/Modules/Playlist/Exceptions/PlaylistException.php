<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Playlist module exception.
 */
final class PlaylistException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_PLAYLIST_NOT_FOUND = 'playlist_not_found';
    public const CODE_MOVIE_NOT_FOUND = 'movie_not_found';
    public const CODE_PLAYLIST_MOVIE_NOT_FOUND = 'playlist_movie_not_found';
    public const CODE_PLAYLIST_CREATE_FAILED = 'playlist_create_failed';
}
