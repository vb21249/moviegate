<?php

declare(strict_types=1);

namespace App\Modules\Movie\Controllers\Api\V1;

use App\Common\Auth\RbacPermission;
use App\Common\Contracts\LocaleResolverInterface;
use App\Common\Controllers\BaseApiController;
use App\Common\Transformers\ApiResponseTransformer;
use App\Modules\Movie\Exceptions\MovieException;
use App\Modules\Movie\Interfaces\MovieServiceInterface;
use App\Modules\Movie\Requests\MovieRequest;

final class MovieController extends BaseApiController
{
    /**
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        return $this->requireBearerAuthWithRbac([
            'watch' => RbacPermission::MOVIE_WATCH,
        ]);
    }

    public function __construct(
        string $id,
        $module,
        ApiResponseTransformer $transformer,
        private readonly MovieServiceInterface $service,
        private readonly LocaleResolverInterface $localeResolver,
        array $config = [],
    ) {
        parent::__construct($id, $module, $transformer, $config);
    }

    /**
     * @return array<string, mixed>
     */
    public function actionIndex(): array
    {
        return $this->success($this->service->index($this->validateQueryRequest())->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionView(int $id): array
    {
        return $this->success($this->service->view($id, $this->validateQueryRequest()->language())->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionWatch(int $id): array
    {
        return $this->success($this->service->watch(
            $id,
            (int) \Yii::$app->user->id,
            $this->validateQueryRequest()->language()
        )->toArray());
    }

    /**
     * @return array<string, mixed>
     */
    public function actionSearch(): array
    {
        return $this->actionIndex();
    }

    /**
     * @return MovieRequest
     */
    private function validateQueryRequest(): MovieRequest
    {
        $request = new MovieRequest();
        $request->load(\Yii::$app->request->queryParams, '');
        $request->language = $this->localeResolver->resolve($this->requestedLanguage($request));

        if (!$request->validate()) {
            throw new MovieException(
                $request->firstErrorMessage(),
                422,
                MovieException::CODE_VALIDATION_ERROR
            );
        }

        return $request;
    }

    private function requestedLanguage(MovieRequest $request): ?string
    {
        if (is_string($request->language) && $request->language !== '') {
            return $request->language;
        }

        $language = \Yii::$app->request->queryParams['lang'] ?? null;

        return is_string($language) ? $language : null;
    }
}
