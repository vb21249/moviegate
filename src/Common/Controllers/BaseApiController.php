<?php

declare(strict_types=1);

namespace App\Common\Controllers;

use App\Common\Dto\ApiResponseDto;
use App\Common\Responses\PaginatedResponse;
use App\Common\Transformers\ApiResponseTransformer;
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
}
