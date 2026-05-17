<?php

declare(strict_types=1);

namespace App\Modules\Comment\Mappers;

use App\Modules\Comment\DTO\CommentDto;
use App\Modules\Comment\Entities\CommentEntity;

/**
 * Comment mapper placeholder.
 */
final class CommentMapper
{
    public function mapToDto(CommentEntity $entity): CommentDto
    {
        return new CommentDto($entity->attributes);
    }
}