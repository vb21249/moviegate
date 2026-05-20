<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Controllers\Api\V1;

use App\Common\Components\JwtBearerAuth;
use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Recommendation\Exceptions\RecommendationException;
use App\Modules\Recommendation\Interfaces\RecommendationServiceInterface;
use App\Modules\Recommendation\Requests\RecommendationRequest;

/**
 * @OA\Tag(
 *     name="Recommendation",
 *     description="Recommendation endpoints"
 * )
 */
final class RecommendationController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtBearerAuth::class,
            'only' => ['index', 'rebuild'],
            'optional' => ['index'],
        ];

        return $behaviors;
    }

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
            $this->service
                ->index(
                    $this->validateRequest(
                        RecommendationRequest::SCENARIO_INDEX,
                        \Yii::$app->request->queryParams
                    ),
                    $this->userId()
                )
                ->toArray()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRebuild(): array
    {
        return $this->success(
            $this->service
                ->rebuild(
                    $this->validateRequest(
                        RecommendationRequest::SCENARIO_REBUILD,
                        \Yii::$app->request->bodyParams
                    ),
                    (int) \Yii::$app->user->id
                )
                ->toArray()
        );
    }

    /**
     * @param string $scenario
     * @param array<string, mixed> $params
     */
    private function validateRequest(string $scenario, array $params): RecommendationRequest
    {
        $request = new RecommendationRequest(['scenario' => $scenario]);
        $request->loadFromArray($params);

        if (!$request->validate()) {
            throw new RecommendationException(
                $request->firstErrorMessage(),
                422,
                RecommendationException::CODE_VALIDATION_ERROR
            );
        }

        return $request;
    }

    private function userId(): ?int
    {
        return \Yii::$app->user->isGuest ? null : (int) \Yii::$app->user->id;
    }
}
