<?php

declare(strict_types=1);

namespace App\Modules\Common\Services;

use App\Common\Services\AbstractService;
use App\Modules\Common\Exceptions\CommonException;
use App\Modules\Common\Interfaces\CommonRepositoryInterface;
use App\Modules\Common\Interfaces\CommonServiceInterface;
use App\Modules\Common\Requests\ApiLogRequest;
use App\Modules\Common\Responses\CommonResponse;

/**
 * Common application service placeholder.
 */
final class CommonService extends AbstractService implements CommonServiceInterface
{
    public function __construct(
        private readonly CommonRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Common',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }

    public function apiLogs(ApiLogRequest $request): CommonResponse
    {
        $items = $this->repository->findApiLogs(
            $request->limit(),
            $request->offset(),
            $request->correlationId(),
            $request->requestMethod(),
            $request->responseStatus(),
            $request->createdFrom(),
            $request->createdTo()
        );
        $total = $this->repository->countApiLogs(
            $request->correlationId(),
            $request->requestMethod(),
            $request->responseStatus(),
            $request->createdFrom(),
            $request->createdTo()
        );

        return new CommonResponse([
            'items' => $items,
            'pagination' => [
                'limit' => $request->limit(),
                'offset' => $request->offset(),
                'total' => $total,
                'has_more' => $request->offset() + $request->limit() < $total,
            ],
        ]);
    }

    public function apiLog(int $id): CommonResponse
    {
        $log = $this->repository->findApiLog($id);

        if ($log === null) {
            throw new CommonException('API log not found.', 404, 'api_log_not_found');
        }

        return new CommonResponse([
            'log' => $log,
        ]);
    }

    public function apiLogSummary(ApiLogRequest $request): CommonResponse
    {
        return new CommonResponse([
            'summary' => $this->repository->apiLogSummary(
                $request->createdFrom(),
                $request->createdTo()
            ),
        ]);
    }
}
