<?php

declare(strict_types=1);

namespace App\Modules\Integration\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Integration\Interfaces\IntegrationServiceInterface;

/**
 * @OA\Tag(
 *     name="Integration",
 *     description="Integration endpoints"
 * )
 */
final class IntegrationController extends BaseApiController
{
    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly IntegrationServiceInterface $service,
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
                'route' => 'Integration/index',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionTmdbSync(): array
    {
        return $this->success(
            $this->service->execute('tmdb-sync', [
                'route' => 'Integration/tmdb-sync',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionSoapPing(): array
    {
        return $this->success(
            $this->service->execute('soap-ping', [
                'route' => 'Integration/soap-ping',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}