<?php

declare(strict_types=1);

namespace App\Modules\Comment\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Comment\Interfaces\CommentServiceInterface;

/**
 * @OA\Tag(
 *     name="Comment",
 *     description="Comment endpoints"
 * )
 */
final class CommentController extends BaseApiController
{
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
        return $this->success(
            $this->service->execute('index', [
                'route' => 'Comment/index',
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
                'route' => 'Comment/create',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionReply(): array
    {
        return $this->success(
            $this->service->execute('reply', [
                'route' => 'Comment/reply',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionLike(): array
    {
        return $this->success(
            $this->service->execute('like', [
                'route' => 'Comment/like',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}