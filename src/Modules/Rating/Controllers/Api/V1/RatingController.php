<?php

declare(strict_types=1);

namespace App\Modules\Rating\Controllers\Api\V1;

use App\Common\Auth\RbacPermission;
use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Rating\Exceptions\RatingException;
use App\Modules\Rating\Interfaces\RatingServiceInterface;
use App\Modules\Rating\Requests\RatingRequest;

/**
 * @OA\Tag(
 *     name="Rating",
 *     description="Rating endpoints"
 * )
 */
final class RatingController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        return $this->requireBearerAuthWithRbac([
            'create' => RbacPermission::RATING_CREATE,
            'update' => RbacPermission::RATING_UPDATE,
            'delete' => RbacPermission::RATING_DELETE,
            'history' => RbacPermission::RATING_HISTORY,
        ]);
    }

    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly RatingServiceInterface $service,
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
            ->index($this->validateRequest(RatingRequest::SCENARIO_INDEX, $this->queryParams()))
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
        return $this->success($this->service
            ->create($this->validateRequest(RatingRequest::SCENARIO_CREATE, $this->bodyParams()), $this->userId())
            ->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionUpdate(int $id): array
    {
        return $this->success($this->service
            ->update($id, $this->validateRequest(RatingRequest::SCENARIO_UPDATE, $this->bodyParams()), $this->userId())
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
    public function actionHistory(): array
    {
        return $this->success($this->service
            ->history($this->validateRequest(RatingRequest::SCENARIO_HISTORY, $this->queryParams()), $this->userId())
            ->toArray());
    }

    /**
     * @param string $scenario
     * @param array<string, mixed> $params
     */
    private function validateRequest(string $scenario, array $params): RatingRequest
    {
        $request = new RatingRequest(['scenario' => $scenario]);
        $request->loadFromArray($params);

        if (!$request->validate()) {
            throw new RatingException(
                $request->firstErrorMessage(),
                422,
                RatingException::CODE_VALIDATION_ERROR
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
