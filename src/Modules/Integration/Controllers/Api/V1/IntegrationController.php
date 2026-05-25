<?php

declare(strict_types=1);

namespace App\Modules\Integration\Controllers\Api\V1;

use App\Common\Auth\RbacPermission;
use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Integration\Exceptions\IntegrationException;
use App\Modules\Integration\Interfaces\IntegrationServiceInterface;
use App\Modules\Integration\Requests\IntegrationRequest;

/**
 * @OA\Tag(
 *     name="Integration",
 *     description="Integration endpoints"
 * )
 */
final class IntegrationController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        return $this->requireBearerAuthWithRbac([
            'index' => RbacPermission::INTEGRATION_ACCESS,
            'tmdb-sync' => RbacPermission::INTEGRATION_SYNC,
            'soap-ping' => RbacPermission::INTEGRATION_ACCESS,
        ]);
    }

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
        return $this->success($this->service->syncTmdb($this->validateTmdbSyncRequest())->toArray());
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

    private function validateTmdbSyncRequest(): IntegrationRequest
    {
        $request = new IntegrationRequest();
        $request->load(\Yii::$app->request->bodyParams, '');

        if (!$request->validate()) {
            throw new IntegrationException(
                $request->firstErrorMessage(),
                422,
                IntegrationException::CODE_VALIDATION_ERROR
            );
        }

        return $request;
    }
}
