<?php

declare(strict_types=1);

namespace App\Modules\Movie\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Movie\Interfaces\MovieServiceInterface;

/**
 * @OA\Tag(
 *     name="Movie",
 *     description="Movie endpoints"
 * )
 */
final class MovieController extends BaseApiController
{
    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly MovieServiceInterface $service,
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
                'route' => 'Movie/index',
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
                'route' => 'Movie/view',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionWatch(): array
    {
        return $this->success(
            $this->service->execute('watch', [
                'route' => 'Movie/watch',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionSearch(): array
    {
        return $this->success(
            $this->service->execute('search', [
                'route' => 'Movie/search',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}