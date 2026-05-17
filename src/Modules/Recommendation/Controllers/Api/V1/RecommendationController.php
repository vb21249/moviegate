<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Recommendation\Interfaces\RecommendationServiceInterface;

/**
 * @OA\Tag(
 *     name="Recommendation",
 *     description="Recommendation endpoints"
 * )
 */
final class RecommendationController extends BaseApiController
{
    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly RecommendationServiceInterface $service,
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
                'route' => 'Recommendation/index',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRebuild(): array
    {
        return $this->success(
            $this->service->execute('rebuild', [
                'route' => 'Recommendation/rebuild',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}