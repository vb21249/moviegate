<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Auth\Exceptions\AuthException;
use App\Modules\Auth\Interfaces\AuthServiceInterface;
use App\Modules\Auth\Requests\AuthRequest;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\LogoutRequest;
use App\Modules\Auth\Requests\RefreshTokenRequest;
use App\Modules\Auth\Requests\RequestPasswordResetRequest;
use App\Modules\Auth\Requests\ResetPasswordRequest;
use App\Modules\Auth\Requests\SignupRequest;
use App\Modules\Auth\Requests\VerifyEmailRequest;

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
        $request = $this->validateRequest(SignupRequest::class);

        return $this->success($this->service->signup($request)->toArray(), statusCode: 201);
    }

    /**
     * @return array<string, mixed>
     */
    public function actionLogin(): array
    {
        $request = $this->validateRequest(LoginRequest::class);

        return $this->success($this->service->login($request)->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionLogout(): array
    {
        $request = $this->validateRequest(LogoutRequest::class);

        return $this->success($this->service->logout($request)->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRefresh(): array
    {
        $request = $this->validateRequest(RefreshTokenRequest::class);

        return $this->success($this->service->refresh($request)->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionVerifyEmail(): array
    {
        $request = $this->validateRequest(VerifyEmailRequest::class);

        return $this->success($this->service->verifyEmail($request)->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionRequestPasswordReset(): array
    {
        $request = $this->validateRequest(RequestPasswordResetRequest::class);

        return $this->success($this->service->requestPasswordReset($request)->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionResetPassword(): array
    {
        $request = $this->validateRequest(ResetPasswordRequest::class);

        return $this->success($this->service->resetPassword($request)->toArray());
    }

    /**
     * @template T of AuthRequest
     *
     * @param class-string<T> $requestClass
     *
     * @return T
     */
    private function validateRequest(string $requestClass): AuthRequest
    {
        /** @var T $request */
        $request = new $requestClass();
        $request->load(\Yii::$app->request->bodyParams, '');

        if (method_exists($request, 'loadFromBody')) {
            $request->loadFromBody(\Yii::$app->request->bodyParams);
        }

        if (!$request->validate()) {
            throw new AuthException(
                $request->firstErrorMessage(),
                422,
                AuthException::CODE_VALIDATION_ERROR
            );
        }

        return $request;
    }
}
