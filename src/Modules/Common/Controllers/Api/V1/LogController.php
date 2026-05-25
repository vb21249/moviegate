<?php

declare(strict_types=1);

namespace App\Modules\Common\Controllers\Api\V1;

use App\Common\Auth\RbacPermission;
use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Common\Exceptions\CommonException;
use App\Modules\Common\Interfaces\CommonServiceInterface;
use App\Modules\Common\Requests\ApiLogRequest;

/**
 * @OA\Tag(
 *     name="Logs",
 *     description="Logging and monitoring endpoints"
 * )
 */
final class LogController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        return $this->requireBearerAuthWithRbac([
            'index' => RbacPermission::LOGGING_ACCESS,
            'view' => RbacPermission::LOGGING_ACCESS,
            'summary' => RbacPermission::LOGGING_ACCESS,
        ]);
    }

    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly CommonServiceInterface $service,
        array $config = [],
    ) {
        parent::__construct($id, $module, $transformer, $config);
    }

    /**
     * @return array<string, mixed>
     */
    public function actionIndex(): array
    {
        return $this->success($this->service->apiLogs($this->validateRequest())->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionView(int $id): array
    {
        return $this->success($this->service->apiLog($id)->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionSummary(): array
    {
        return $this->success($this->service->apiLogSummary($this->validateRequest())->toArray());
    }

    private function validateRequest(): ApiLogRequest
    {
        $request = new ApiLogRequest();
        $request->loadFromArray(\Yii::$app->request->queryParams);

        if (!$request->validate()) {
            throw new CommonException(
                $request->firstErrorMessage(),
                422,
                'api_log_query_validation_error'
            );
        }

        return $request;
    }
}
