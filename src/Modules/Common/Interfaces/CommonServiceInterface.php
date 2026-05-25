<?php

declare(strict_types=1);

namespace App\Modules\Common\Interfaces;

use App\Modules\Common\Requests\ApiLogRequest;
use App\Modules\Common\Responses\CommonResponse;

/**
 * Common application service contract.
 */
interface CommonServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;

    public function apiLogs(ApiLogRequest $request): CommonResponse;

    public function apiLog(int $id): CommonResponse;

    public function apiLogSummary(ApiLogRequest $request): CommonResponse;
}
