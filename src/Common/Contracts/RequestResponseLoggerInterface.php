<?php

declare(strict_types=1);

namespace App\Common\Contracts;

use yii\web\Request;
use yii\web\Response;

/**
 * Logs inbound and outbound traffic.
 */
interface RequestResponseLoggerInterface
{
    /**
     * @param Request $request
     */
    public function logRequest(Request $request): void;

    /**
     * @param Response $response
     */
    public function logResponse(Response $response): void;
}
