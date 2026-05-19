<?php

declare(strict_types=1);

namespace App\Modules\Review\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Review\Exceptions\ReviewException;
use App\Modules\Review\Interfaces\ReviewServiceInterface;
use App\Modules\Review\Requests\ReviewRequest;

/**
 * @OA\Tag(
 *     name="Review",
 *     description="Review endpoints"
 * )
 */
final class ReviewController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        return $this->requireBearerAuth(['create', 'update', 'delete']);
    }

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
        return $this->success($this->service
            ->index($this->validateRequest(ReviewRequest::SCENARIO_INDEX, $this->queryParams()))
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
    public function actionCreate(): array
    {
        return $this->success(
            $this->service
                ->create($this->validateRequest(ReviewRequest::SCENARIO_CREATE, $this->bodyParams()), $this->userId())
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
            ->update($id, $this->validateRequest(ReviewRequest::SCENARIO_UPDATE, $this->bodyParams()), $this->userId())
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
     * @param string $scenario
     * @param array<string, mixed> $params
     *
     * @return ReviewRequest
     */
    private function validateRequest(string $scenario, array $params): ReviewRequest
    {
        $request = new ReviewRequest(['scenario' => $scenario]);
        $request->loadFromArray($params);

        if (!$request->validate()) {
            throw new ReviewException(
                $request->firstErrorMessage(),
                422,
                ReviewException::CODE_VALIDATION_ERROR
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
