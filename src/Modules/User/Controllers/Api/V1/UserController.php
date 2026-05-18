<?php

declare(strict_types=1);

namespace App\Modules\User\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\User\Exceptions\UserException;
use App\Modules\User\Interfaces\UserServiceInterface;
use App\Modules\User\Requests\UserRequest;

final class UserController extends BaseApiController
{
    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly UserServiceInterface $service,
        array $config = [],
    ) {
        parent::__construct($id, $module, $transformer, $config);
    }

    /**
     * @return array<string, mixed>
     */
    public function actionIndex(): array
    {
        return $this->success($this->service->index($this->validateQueryRequest())->toArray());
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
    public function actionActivity(int $id): array
    {
        return $this->success($this->service->activity($id, $this->validateQueryRequest())->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRecentRatings(int $id): array
    {
        return $this->success($this->service->recentRatings($id, $this->validateQueryRequest())->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionPosts(int $id): array
    {
        return $this->success($this->service->posts($id, $this->validateQueryRequest())->toArray());
    }

    /**
     * @return UserRequest
     */
    private function validateQueryRequest(): UserRequest
    {
        $request = new UserRequest();
        $request->load(\Yii::$app->request->queryParams, '');

        if (!$request->validate()) {
            throw new UserException(
                $request->firstErrorMessage(),
                422,
                UserException::CODE_VALIDATION_ERROR
            );
        }

        return $request;
    }
}
