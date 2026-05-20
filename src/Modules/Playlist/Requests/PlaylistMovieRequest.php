<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Requests;

use App\Common\Requests\BaseRequest;

/**
 * Request model for adding a movie to a playlist.
 */
final class PlaylistMovieRequest extends BaseRequest
{
    public int|string|null $movie_id = null;
    public int|string|null $position = null;

    /**
     * @return array<int, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            ['movie_id', 'required'],
            ['movie_id', 'integer', 'min' => 1],
            ['position', 'integer', 'min' => 0],
        ];
    }

    /**
     * @param array<string, mixed> $input
     */
    public function loadFromArray(array $input): void
    {
        $this->load($input, '');
    }

    /**
     * @return int
     */
    public function movieId(): int
    {
        return (int) $this->movie_id;
    }

    /**
     * @return int|null
     */
    public function position(): ?int
    {
        return $this->position !== null && $this->position !== '' ? (int) $this->position : null;
    }

    /**
     * @return string
     */
    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }
}
