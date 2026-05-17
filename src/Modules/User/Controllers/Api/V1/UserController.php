<?php

declare(strict_types=1);

namespace App\Modules\User\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\User\Interfaces\UserServiceInterface;

/**
 * @OA\Tag(
 *     name="User",
 *     description="User endpoints"
 * )
 */
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
        return $this->success(
            $this->service->execute('index', [
                'route' => 'User/index',
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
                'route' => 'User/view',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionActivity(): array
    {
        return $this->success(
            $this->service->execute('activity', [
                'route' => 'User/activity',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRecentRatings(): array
    {
        return $this->success(
            $this->service->execute('recent-ratings', [
                'route' => 'User/recent-ratings',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionPosts(): array
    {
        return $this->success(
            $this->service->execute('posts', [
                'route' => 'User/posts',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}