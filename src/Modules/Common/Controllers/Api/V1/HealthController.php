<?php

declare(strict_types=1);

namespace App\Modules\Common\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Common\Interfaces\CommonServiceInterface;

/**
 * @OA\Tag(
 *     name="Common",
 *     description="Common endpoints"
 * )
 */
final class HealthController extends BaseApiController
{
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
        return $this->success(
            $this->service->execute('index', [
                'route' => 'Common/index',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}