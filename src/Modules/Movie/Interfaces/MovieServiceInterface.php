<?php

declare(strict_types=1);

namespace App\Modules\Movie\Interfaces;

use App\Modules\Movie\Requests\MovieRequest;
use App\Modules\Movie\Responses\MovieResponse;

/**
 * Movie application service contract.
 */
interface MovieServiceInterface
{
    /**
     * Returns published movie catalog items.
     *
     * @param MovieRequest $request
     *
     * @return MovieResponse
     */
    public function index(MovieRequest $request): MovieResponse;

    /**
     * Returns a published movie detail by id.
     *
     * @param int $movieId
     *
     * @return MovieResponse
     */
    public function view(int $movieId, ?string $language = null): MovieResponse;

    /**
     * Marks a movie as watched for a user.
     *
     * @param int $movieId
     * @param int $userId
     *
     * @return MovieResponse
     */
    public function watch(int $movieId, int $userId, ?string $language = null): MovieResponse;
}
