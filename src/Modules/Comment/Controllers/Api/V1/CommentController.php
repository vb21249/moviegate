<?php

declare(strict_types=1);

namespace App\Modules\Comment\Controllers\Api\V1;

use App\Common\Auth\RbacPermission;
use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Comment\Exceptions\CommentException;
use App\Modules\Comment\Interfaces\CommentServiceInterface;
use App\Modules\Comment\Requests\CommentRequest;

/**
 * @OA\Tag(
 *     name="Comment",
 *     description="Comment endpoints"
 * )
 */
final class CommentController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        return $this->requireBearerAuthWithRbac([
            'create' => RbacPermission::COMMENT_CREATE,
            'reply' => RbacPermission::COMMENT_REPLY,
            'update' => RbacPermission::COMMENT_UPDATE,
            'delete' => RbacPermission::COMMENT_DELETE,
            'like' => RbacPermission::COMMENT_LIKE,
        ]);
    }

    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly CommentServiceInterface $service,
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
            ->index($this->validateRequest(CommentRequest::SCENARIO_INDEX, $this->queryParams()))
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
                ->create($this->validateRequest(CommentRequest::SCENARIO_CREATE, $this->bodyParams()), $this->userId())
                ->toArray(),
            statusCode: 201
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionReply(int $id): array
    {
        return $this->success(
            $this->service
                ->reply($id, $this->validateRequest(CommentRequest::SCENARIO_REPLY, $this->bodyParams()), $this->userId())
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
            ->update($id, $this->validateRequest(CommentRequest::SCENARIO_UPDATE, $this->bodyParams()), $this->userId())
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
    public function actionLike(int $id): array
    {
        return $this->success($this->service->like($id, $this->userId())->toArray());
    }

    /**
     * @param string $scenario
     * @param array<string, mixed> $params
     */
    private function validateRequest(string $scenario, array $params): CommentRequest
    {
        $request = new CommentRequest(['scenario' => $scenario]);
        $request->loadFromArray($params);

        if (!$request->validate()) {
            throw new CommentException(
                $request->firstErrorMessage(),
                422,
                CommentException::CODE_VALIDATION_ERROR
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
