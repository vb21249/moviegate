<?php

declare(strict_types=1);

namespace App\Modules\Notification\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Notification\Interfaces\NotificationServiceInterface;

/**
 * @OA\Tag(
 *     name="Notification",
 *     description="Notification endpoints"
 * )
 */
final class NotificationController extends BaseApiController
{
    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly NotificationServiceInterface $service,
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
                'route' => 'Notification/index',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionMarkRead(): array
    {
        return $this->success(
            $this->service->execute('mark-read', [
                'route' => 'Notification/mark-read',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}