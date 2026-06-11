<?php

declare(strict_types=1);

namespace App\Modules\Common\Controllers\Api\V1;

use App\Common\OpenApi\OpenApiFactory;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;

final class DocsController extends Controller
{
    public function __construct(
        string $id,
        $module,
        private readonly OpenApiFactory $openApiFactory,
        array $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): string
    {
        $response = \Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        $specUrl = $this->normalizeSpecUrl();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MovieGate Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #f6f7fb;
        }

        #swagger-ui {
            max-width: 1400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.ui = SwaggerUIBundle({
            url: "{$specUrl}",
            dom_id: '#swagger-ui',
            deepLinking: true,
            displayRequestDuration: true,
            presets: [SwaggerUIBundle.presets.apis],
            layout: "BaseLayout"
        });
    </script>
</body>
</html>
HTML;
    }

    public function actionOpenapi(): string
    {
        $response = \Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return Json::encode(
            $this->openApiFactory->build($this->normalizedBaseUrl()),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    private function normalizedBaseUrl(): string
    {
        $baseUrl = rtrim((string) \Yii::$app->request->baseUrl, '/');

        return $baseUrl === '' ? '/' : $baseUrl;
    }

    private function normalizeSpecUrl(): string
    {
        $baseUrl = rtrim((string) \Yii::$app->request->baseUrl, '/');
        $path = ($baseUrl === '' ? '' : $baseUrl) . '/docs/openapi.json';

        return Html::encode($path === '' ? '/docs/openapi.json' : $path);
    }
}
