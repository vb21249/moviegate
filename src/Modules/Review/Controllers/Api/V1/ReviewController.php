<?php

declare(strict_types=1);

namespace App\Modules\Review\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Review\Interfaces\ReviewServiceInterface;

/**
 * @OA\Tag(
 *     name="Review",
 *     description="Review endpoints"
 * )
 */
final class ReviewController extends BaseApiController
{
    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly ReviewServiceInterface $service,
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
                'route' => 'Review/index',
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
                'route' => 'Review/view',
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
                'route' => 'Review/create',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}