<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Interfaces;

use App\Modules\Playlist\Requests\PlaylistMovieRequest;
use App\Modules\Playlist\Requests\PlaylistRequest;
use App\Modules\Playlist\Responses\PlaylistResponse;

/**
 * Playlist application service contract.
 */
interface PlaylistServiceInterface
{
    /**
     * Returns authenticated user's playlists.
     *
     * @param PlaylistRequest $request
     * @param int $userId
     *
     * @return PlaylistResponse
     */
    public function index(PlaylistRequest $request, int $userId): PlaylistResponse;

    /**
     * Returns an accessible playlist with movies.
     *
     * @param int $playlistId
     * @param int $userId
     *
     * @return PlaylistResponse
     */
    public function view(int $playlistId, int $userId): PlaylistResponse;

    /**
     * Creates a playlist for the authenticated user.
     *
     * @param PlaylistRequest $request
     * @param int $userId
     *
     * @return PlaylistResponse
     */
    public function create(PlaylistRequest $request, int $userId): PlaylistResponse;

    /**
     * Updates a playlist owned by the authenticated user.
     *
     * @param int $playlistId
     * @param PlaylistRequest $request
     * @param int $userId
     *
     * @return PlaylistResponse
     */
    public function update(int $playlistId, PlaylistRequest $request, int $userId): PlaylistResponse;

    /**
     * Soft-deletes a playlist owned by the authenticated user.
     *
     * @param int $playlistId
     * @param int $userId
     *
     * @return PlaylistResponse
     */
    public function delete(int $playlistId, int $userId): PlaylistResponse;

    /**
     * Adds an active movie to a playlist.
     *
     * @param int $playlistId
     * @param PlaylistMovieRequest $request
     * @param int $userId
     *
     * @return PlaylistResponse
     */
    public function addMovie(int $playlistId, PlaylistMovieRequest $request, int $userId): PlaylistResponse;

    /**
     * Removes a movie from a playlist.
     *
     * @param int $playlistId
     * @param int $movieId
     * @param int $userId
     *
     * @return PlaylistResponse
     */
    public function removeMovie(int $playlistId, int $movieId, int $userId): PlaylistResponse;
}
