<?php

declare(strict_types=1);

namespace App\Modules\Comment\Interfaces;

use App\Modules\Comment\Requests\CommentRequest;
use App\Modules\Comment\Responses\CommentResponse;

/**
 * Comment application service contract.
 */
interface CommentServiceInterface
{
    public function index(CommentRequest $request): CommentResponse;

    public function view(int $commentId): CommentResponse;

    public function create(CommentRequest $request, int $userId): CommentResponse;

    public function reply(int $parentId, CommentRequest $request, int $userId): CommentResponse;

    public function update(int $commentId, CommentRequest $request, int $userId): CommentResponse;

    public function delete(int $commentId, int $userId): CommentResponse;

    public function like(int $commentId, int $userId): CommentResponse;
}
