<?php

declare(strict_types=1);

namespace App\Modules\Notification\Controllers\Api\V1;

use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Notification\Exceptions\NotificationException;
use App\Modules\Notification\Interfaces\NotificationServiceInterface;
use App\Modules\Notification\Requests\NotificationRequest;

/**
 * @OA\Tag(
 *     name="Notification",
 *     description="Notification endpoints"
 * )
 */
final class NotificationController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        return $this->requireBearerAuth(['index', 'view', 'mark-read', 'mark-all-read']);
    }

    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly NotificationServiceInterface $service,
        array $config = [],
    ) {
        parent::__construct($id, $module, $transformer, $config);
    }

    /**
     * @return array<string, mixed>
     */
    public function actionIndex(): array
    {
        return $this->success($this->service
            ->index($this->validateRequest(NotificationRequest::SCENARIO_INDEX, $this->queryParams()), $this->userId())
            ->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionView(int $id): array
    {
        return $this->success($this->service->view($id, $this->userId())->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionMarkRead(int $id): array
    {
        return $this->success($this->service->markRead($id, $this->userId())->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionMarkAllRead(): array
    {
        return $this->success($this->service
            ->markAllRead(
                $this->validateRequest(NotificationRequest::SCENARIO_MARK_ALL_READ, $this->bodyParams()),
                $this->userId()
            )
            ->toArray());
    }

    /**
     * @param string $scenario
     * @param array<string, mixed> $params
     */
    private function validateRequest(string $scenario, array $params): NotificationRequest
    {
        $request = new NotificationRequest(['scenario' => $scenario]);
        $request->loadFromArray($params);

        if (!$request->validate()) {
            throw new NotificationException(
                $request->firstErrorMessage(),
                422,
                NotificationException::CODE_VALIDATION_ERROR
            );
        }

        return $request;
    }

    private function userId(): int
    {
        return (int) \Yii::$app->user->id;
    }

    /**
     * @return array<string, mixed>
     */
    private function queryParams(): array
    {
        return \Yii::$app->request->queryParams;
    }

    /**
     * @return array<string, mixed>
     */
    private function bodyParams(): array
    {
        $bodyParams = \Yii::$app->request->bodyParams;

        return is_array($bodyParams) ? $bodyParams : [];
    }
}
