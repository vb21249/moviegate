<?php

declare(strict_types=1);

namespace App\Modules\Movie\Services;

use App\Common\Services\AbstractService;
use App\Modules\Movie\Exceptions\MovieException;
use App\Modules\Movie\Interfaces\MovieRepositoryInterface;
use App\Modules\Movie\Interfaces\MovieServiceInterface;
use App\Modules\Movie\Mappers\MovieMapper;
use App\Modules\Movie\Requests\MovieRequest;
use App\Modules\Movie\Responses\MovieResponse;
use App\Modules\Movie\Transformers\MovieTransformer;

/**
 * Movie application service for catalog reads and watched marks.
 */
final class MovieService extends AbstractService implements MovieServiceInterface
{
    public function __construct(
        private readonly MovieRepositoryInterface $repository,
        private readonly MovieMapper $mapper,
        private readonly MovieTransformer $transformer,
    ) {
    }

    public function index(MovieRequest $request): MovieResponse
    {
        $movies = array_map(
            fn (array $movie): array => $this->mapper->mapMovie($movie)->toArray(),
            $this->repository->findPublishedMovies(
                $request->limit(),
                $request->offset(),
                $request->query(),
                $request->language()
            )
        );

        return new MovieResponse([
            'items' => $movies,
            'pagination' => $this->pagination(
                $request,
                $this->repository->countPublishedMovies($request->query(), $request->language())
            ),
        ]);
    }

    public function view(int $movieId, ?string $language = null): MovieResponse
    {
        $movie = $this->findMovieOrFail($movieId, $language);

        return new MovieResponse([
            'movie' => $this->mapper
                ->mapMovie($movie, $this->repository->getMovieStats($movieId))
                ->toArray(),
        ]);
    }

    public function watch(int $movieId, int $userId, ?string $language = null): MovieResponse
    {
        $movie = $this->findMovieOrFail($movieId, $language);
        $this->repository->markWatched($movieId, $userId);

        return new MovieResponse(
            $this->transformer->watchedPayload($movieId, $userId, $movie)
        );
    }

    /**
     * @param int $movieId
     *
     * @return array<string, mixed>
     */
    private function findMovieOrFail(int $movieId, ?string $language = null): array
    {
        $movie = $this->repository->findPublishedMovie($movieId, $language);

        if ($movie === null) {
            throw new MovieException('Movie not found', 404, MovieException::CODE_MOVIE_NOT_FOUND);
        }

        return $movie;
    }

    /**
     * @param MovieRequest $request
     * @param int $total
     *
     * @return array<string, int|bool>
     */
    private function pagination(MovieRequest $request, int $total): array
    {
        $limit = $request->limit();
        $offset = $request->offset();

        return [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
            'has_more' => $offset + $limit < $total,
        ];
    }
}
