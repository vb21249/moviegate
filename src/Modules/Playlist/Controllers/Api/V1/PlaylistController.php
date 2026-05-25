<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Controllers\Api\V1;

use App\Common\Auth\RbacPermission;
use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Playlist\Exceptions\PlaylistException;
use App\Modules\Playlist\Interfaces\PlaylistServiceInterface;
use App\Modules\Playlist\Requests\PlaylistMovieRequest;
use App\Modules\Playlist\Requests\PlaylistRequest;

/**
 * @OA\Tag(
 *     name="Playlist",
 *     description="Playlist endpoints"
 * )
 */
final class PlaylistController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        return $this->requireBearerAuthWithRbac([
            'index' => RbacPermission::PLAYLIST_READ,
            'view' => RbacPermission::PLAYLIST_READ,
            'create' => RbacPermission::PLAYLIST_CREATE,
            'update' => RbacPermission::PLAYLIST_UPDATE,
            'delete' => RbacPermission::PLAYLIST_DELETE,
            'add-movie' => RbacPermission::PLAYLIST_MOVIE_MANAGE,
            'remove-movie' => RbacPermission::PLAYLIST_MOVIE_MANAGE,
        ]);
    }

    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly PlaylistServiceInterface $service,
        array $config = [],
    ) {
        parent::__construct($id, $module, $transformer, $config);
    }

    /**
     * @return array<string, mixed>
     */
    public function actionIndex(): array
    {
        return $this->success($this->service
            ->index($this->validatePlaylistRequest(PlaylistRequest::SCENARIO_INDEX, $this->queryParams()), $this->userId())
            ->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionView(int $id): array
    {
        return $this->success($this->service->view($id, $this->userId())->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionCreate(): array
    {
        return $this->success(
            $this->service
                ->create($this->validatePlaylistRequest(PlaylistRequest::SCENARIO_CREATE, $this->bodyParams()), $this->userId())
                ->toArray(),
            statusCode: 201
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionUpdate(int $id): array
    {
        return $this->success($this->service
            ->update($id, $this->validatePlaylistRequest(PlaylistRequest::SCENARIO_UPDATE, $this->bodyParams()), $this->userId())
            ->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionDelete(int $id): array
    {
        return $this->success($this->service->delete($id, $this->userId())->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionAddMovie(int $id): array
    {
        return $this->success(
            $this->service->addMovie($id, $this->validatePlaylistMovieRequest(), $this->userId())->toArray(),
            statusCode: 201
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRemoveMovie(int $id, int $movieId): array
    {
        return $this->success($this->service->removeMovie($id, $movieId, $this->userId())->toArray());
    }

    /**
     * @param string $scenario
     * @param array<string, mixed> $params
     *
     * @return PlaylistRequest
     */
    private function validatePlaylistRequest(string $scenario, array $params): PlaylistRequest
    {
        $request = new PlaylistRequest(['scenario' => $scenario]);
        $request->loadFromArray($params);

        if (!$request->validate()) {
            throw new PlaylistException(
                $request->firstErrorMessage(),
                422,
                PlaylistException::CODE_VALIDATION_ERROR
            );
        }

        return $request;
    }

    private function validatePlaylistMovieRequest(): PlaylistMovieRequest
    {
        $request = new PlaylistMovieRequest();
        $request->loadFromArray($this->bodyParams());

        if (!$request->validate()) {
            throw new PlaylistException(
                $request->firstErrorMessage(),
                422,
                PlaylistException::CODE_VALIDATION_ERROR
            );
        }

        return $request;
    }

    private function userId(): int
    {
        return (int) \Yii::$app->user->id;
    }

    /**
     * @return array<string, mixed>
     */
    private function queryParams(): array
    {
        return \Yii::$app->request->queryParams;
    }

    /**
     * @return array<string, mixed>
     */
    private function bodyParams(): array
    {
        $bodyParams = \Yii::$app->request->bodyParams;

        return is_array($bodyParams) ? $bodyParams : [];
    }
}
