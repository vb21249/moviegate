<?php

declare(strict_types=1);

namespace App\Modules\Comment\Enums;

/**
 * Comment status enum.
 */
enum CommentStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}