<?php

declare(strict_types=1);

namespace App\Common\Controllers;

use App\Common\Exceptions\ApiException;
use App\Common\Components\JwtBearerAuth;
use App\Common\Dto\ApiResponseDto;
use App\Common\Responses\PaginatedResponse;
use App\Common\Transformers\ApiResponseTransformer;
use yii\filters\AccessControl;
use yii\rest\Controller;

/**
 * Base REST controller.
 */
abstract class BaseApiController extends Controller
{
    private const HTTP_OK = 200;

    public function __construct(
        string $id,
        $module,
        private readonly ApiResponseTransformer $transformer,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * @param array<string, mixed>|list<mixed>|null $data
     * @param array<string, mixed>|null $meta
     * @param int $statusCode
     *
     * @return array
     */
    protected function success(array|null $data = null, ?array $meta = null, int $statusCode = 200): array
    {
        \Yii::$app->response->statusCode = $statusCode;

        return $this->transformer->transform(new ApiResponseDto(true, $data, $meta));
    }

    /**
     * @param string $message
     * @param string $code
     * @param int $statusCode
     * @return array
     */
    protected function error(string $message, string $code = 'validation_error', int $statusCode = 400): array
    {
        \Yii::$app->response->statusCode = $statusCode;

        return $this->transformer->transform(new ApiResponseDto(false, null, null, [
            'code' => $code,
            'message' => $message,
        ]));
    }

    /**
     * @param PaginatedResponse $response
     * @return array
     */
    protected function paginated(PaginatedResponse $response): array
    {
        \Yii::$app->response->statusCode = self::HTTP_OK;

        return $this->success($response->items, $response->toArray()['pagination']);
    }

    /**
     * @param list<string> $only
     *
     * @return array<string, mixed>
     */
    protected function requireBearerAuth(array $only): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtBearerAuth::class,
            'only' => $only,
        ];

        return $behaviors;
    }

    /**
     * @param array<string, string> $permissionsByAction
     *
     * @return array<string, mixed>
     */
    protected function requireBearerAuthWithRbac(array $permissionsByAction): array
    {
        return $this->withRbac($this->requireBearerAuth(array_keys($permissionsByAction)), $permissionsByAction);
    }

    /**
     * @param array<string, mixed> $behaviors
     * @param array<string, string> $permissionsByAction
     *
     * @return array<string, mixed>
     */
    protected function withRbac(array $behaviors, array $permissionsByAction): array
    {
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => array_keys($permissionsByAction),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => static function ($rule, $action) use ($permissionsByAction): bool {
                        $permission = $permissionsByAction[$action->id] ?? null;

                        return $permission !== null && \Yii::$app->user->can($permission);
                    },
                ],
            ],
            'denyCallback' => static function (): void {
                throw new ApiException(
                    'You do not have permission to perform this action.',
                    403,
                    'forbidden'
                );
            },
        ];

        return $behaviors;
    }
}
