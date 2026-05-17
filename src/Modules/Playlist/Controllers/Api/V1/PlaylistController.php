<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Playlist\Interfaces\PlaylistServiceInterface;

/**
 * @OA\Tag(
 *     name="Playlist",
 *     description="Playlist endpoints"
 * )
 */
final class PlaylistController extends BaseApiController
{
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
        return $this->success(
            $this->service->execute('index', [
                'route' => 'Playlist/index',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionView(): array
    {
        return $this->success(
            $this->service->execute('view', [
                'route' => 'Playlist/view',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionCreate(): array
    {
        return $this->success(
            $this->service->execute('create', [
                'route' => 'Playlist/create',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionAddMovie(): array
    {
        return $this->success(
            $this->service->execute('add-movie', [
                'route' => 'Playlist/add-movie',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRemoveMovie(): array
    {
        return $this->success(
            $this->service->execute('remove-movie', [
                'route' => 'Playlist/remove-movie',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}