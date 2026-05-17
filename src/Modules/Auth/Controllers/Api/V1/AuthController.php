<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Auth\Interfaces\AuthServiceInterface;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Auth endpoints"
 * )
 */
final class AuthController extends BaseApiController
{
    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly AuthServiceInterface $service,
        array $config = [],
    ) {
        parent::__construct($id, $module, $transformer, $config);
    }

    /**
     * @return array<string, mixed>
     */
    public function actionSignup(): array
    {
        return $this->success(
            $this->service->execute('signup', [
                'route' => 'Auth/signup',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionLogin(): array
    {
        return $this->success(
            $this->service->execute('login', [
                'route' => 'Auth/login',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionLogout(): array
    {
        return $this->success(
            $this->service->execute('logout', [
                'route' => 'Auth/logout',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRefresh(): array
    {
        return $this->success(
            $this->service->execute('refresh', [
                'route' => 'Auth/refresh',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionVerifyEmail(): array
    {
        return $this->success(
            $this->service->execute('verify-email', [
                'route' => 'Auth/verify-email',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRequestPasswordReset(): array
    {
        return $this->success(
            $this->service->execute('request-password-reset', [
                'route' => 'Auth/request-password-reset',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function actionResetPassword(): array
    {
        return $this->success(
            $this->service->execute('reset-password', [
                'route' => 'Auth/reset-password',
                'query' => \Yii::$app->request->queryParams,
                'body' => \Yii::$app->request->bodyParams,
            ])
        );
    }
}