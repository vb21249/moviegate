<?php

declare(strict_types=1);

namespace App\Modules\Comment\Exceptions;

use App\Common\Exceptions\ApiException;

/**
 * Comment module exception.
 */
final class CommentException extends ApiException
{
    public const CODE_VALIDATION_ERROR = 'validation_error';
    public const CODE_COMMENT_NOT_FOUND = 'comment_not_found';
    public const CODE_MOVIE_NOT_FOUND = 'movie_not_found';
    public const CODE_REVIEW_NOT_FOUND = 'review_not_found';
    public const CODE_COMMENT_CREATE_FAILED = 'comment_create_failed';
}
