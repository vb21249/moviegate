<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\User\Exceptions\UserException;
use App\Modules\User\Interfaces\UserRepositoryInterface;
use App\Modules\User\Mappers\UserMapper;
use App\Modules\User\Services\UserService;
use App\Modules\User\Transformers\UserTransformer;
use PHPUnit\Framework\TestCase;

final class UserServiceTest extends TestCase
{
    public function testViewReturnsPublicProfileWithoutPrivateFields(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->method('findPublicProfile')
            ->with(7)
            ->willReturn([
                'id' => 7,
                'username' => 'student',
                'avatar_url' => null,
                'bio' => 'Movie fan',
                'status' => 'active',
                'created_at' => '2026-05-18 10:00:00',
            ]);
        $repository
            ->method('getProfileStats')
            ->with(7)
            ->willReturn([
                'followers_count' => 2,
                'following_count' => 3,
                'ratings_count' => 4,
                'reviews_count' => 5,
                'comments_count' => 6,
            ]);

        $service = new UserService($repository, new UserMapper(), new UserTransformer());

        $payload = $service->view(7)->toArray();

        self::assertSame('student', $payload['user']['username']);
        self::assertSame(4, $payload['user']['stats']['ratings_count']);
        self::assertArrayNotHasKey('email', $payload['user']);
        self::assertArrayNotHasKey('password_hash', $payload['user']);
    }

    public function testViewThrowsWhenUserIsMissing(): void
    {
        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository
            ->method('findPublicProfile')
            ->with(404)
            ->willReturn(null);

        $service = new UserService($repository, new UserMapper(), new UserTransformer());

        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User not found');

        $service->view(404);
    }
}
