<?php

declare(strict_types=1);

namespace App\Common\Components;

use App\Common\Exceptions\ApiException;
use Throwable;
use Yii;
use yii\web\ErrorHandler;
use yii\web\Response;

/**
 * Converts uncaught exceptions into unified JSON responses.
 */
final class ApiErrorHandler extends ErrorHandler
{
    /**
     * @param Throwable $exception
     */
    protected function renderException($exception): void
    {
        if (Yii::$app->response instanceof Response) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }

        $statusCode = $exception instanceof ApiException ? $exception->getStatusCode() : 500;
        $code = $exception instanceof ApiException ? $exception->getErrorCode() : 'internal_error';

        Yii::$app->response->statusCode = $statusCode;
        Yii::$app->response->data = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $exception->getMessage(),
            ],
        ];

        Yii::error($exception);
        Yii::$app->response->send();
    }
}
