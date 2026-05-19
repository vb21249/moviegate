<?php

declare(strict_types=1);

namespace App\Modules\Feed\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Feed\Exceptions\FeedException;
use App\Modules\Feed\Interfaces\FeedServiceInterface;
use App\Modules\Feed\Requests\FeedRequest;

/**
 * @OA\Tag(
 *     name="Feed",
 *     description="Feed endpoints"
 * )
 */
final class FeedController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        return $this->requireBearerAuth(['mine']);
    }

    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly FeedServiceInterface $service,
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
            ->index($this->validateRequest(FeedRequest::SCENARIO_INDEX, $this->queryParams()))
            ->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionView(int $id): array
    {
        return $this->success($this->service->view($id)->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionMine(): array
    {
        return $this->success($this->service
            ->mine($this->validateRequest(FeedRequest::SCENARIO_MINE, $this->queryParams()), $this->userId())
            ->toArray());
    }

    /**
     * @param string $scenario
     * @param array<string, mixed> $params
     */
    private function validateRequest(string $scenario, array $params): FeedRequest
    {
        $request = new FeedRequest(['scenario' => $scenario]);
        $request->loadFromArray($params);

        if (!$request->validate()) {
            throw new FeedException(
                $request->firstErrorMessage(),
                422,
                FeedException::CODE_VALIDATION_ERROR
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
}
