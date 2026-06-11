<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modules\Common\Exceptions\CommonException;
use App\Modules\Common\Interfaces\CommonRepositoryInterface;
use App\Modules\Common\Requests\ApiLogRequest;
use App\Modules\Common\Services\CommonService;
use PHPUnit\Framework\TestCase;

final class ApiLogMonitoringTest extends TestCase
{
    public function testApiLogRequestValidatesAndNormalizesFilters(): void
    {
        $request = new ApiLogRequest();
        $request->loadFromArray([
            'limit' => '10',
            'offset' => '5',
            'request_method' => ' post ',
            'response_status' => '500',
            'created_from' => '2026-05-25',
        ]);

        self::assertTrue($request->validate(), implode(', ', $request->getErrorSummary(true)));
        self::assertSame(10, $request->limit());
        self::assertSame(5, $request->offset());
        self::assertSame('POST', $request->requestMethod());
        self::assertSame(500, $request->responseStatus());
        self::assertSame('2026-05-25 00:00:00', $request->createdFrom());
    }

    public function testApiLogsReturnsItemsAndPagination(): void
    {
        $request = new ApiLogRequest();
        $request->loadFromArray([
            'limit' => 2,
            'offset' => 1,
            'response_status' => 200,
        ]);
        self::assertTrue($request->validate());

        $repository = $this->createMock(CommonRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findApiLogs')
            ->with(2, 1, null, null, 200, null, null)
            ->willReturn([
                [
                    'id' => 10,
                    'correlation_id' => 'correlation-id',
                    'request_method' => 'GET',
                    'request_uri' => '/health',
                    'request_body' => null,
                    'response_status' => 200,
                    'response_body' => ['status' => 'ok'],
                    'created_at' => '2026-05-25 10:00:00',
                ],
            ]);
        $repository
            ->expects($this->once())
            ->method('countApiLogs')
            ->with(null, null, 200, null, null)
            ->willReturn(4);

        $response = (new CommonService($repository))->apiLogs($request)->toArray();

        self::assertSame(10, $response['items'][0]['id']);
        self::assertSame([
            'limit' => 2,
            'offset' => 1,
            'total' => 4,
            'has_more' => true,
        ], $response['pagination']);
    }

    public function testApiLogThrowsWhenLogDoesNotExist(): void
    {
        $repository = $this->createMock(CommonRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('findApiLog')
            ->with(404)
            ->willReturn(null);

        try {
            (new CommonService($repository))->apiLog(404);
            self::fail('Expected CommonException.');
        } catch (CommonException $exception) {
            self::assertSame(404, $exception->getStatusCode());
            self::assertSame('api_log_not_found', $exception->getErrorCode());
        }
    }

    public function testApiLogSummaryUsesDateFilters(): void
    {
        $request = new ApiLogRequest();
        $request->loadFromArray([
            'created_from' => '2026-05-25 00:00:00',
            'created_to' => '2026-05-25 23:59:59',
        ]);
        self::assertTrue($request->validate());

        $repository = $this->createMock(CommonRepositoryInterface::class);
        $repository
            ->expects($this->once())
            ->method('apiLogSummary')
            ->with('2026-05-25 00:00:00', '2026-05-25 23:59:59')
            ->willReturn([
                'total' => 2,
                'by_status' => ['200' => 1, '500' => 1],
                'by_family' => ['1xx' => 0, '2xx' => 1, '3xx' => 0, '4xx' => 0, '5xx' => 1],
                'latest_created_at' => '2026-05-25 12:00:00',
            ]);

        $response = (new CommonService($repository))->apiLogSummary($request)->toArray();

        self::assertSame(2, $response['summary']['total']);
        self::assertSame(1, $response['summary']['by_family']['5xx']);
    }
}
