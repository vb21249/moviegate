<?php

declare(strict_types=1);

namespace App\Common\Bootstrap;

use App\Common\Contracts\CorrelationIdProviderInterface;
use App\Common\Contracts\RequestResponseLoggerInterface;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\web\Response;

/**
 * Registers request lifecycle hooks required by the application.
 */
final class ApplicationBootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        if ($app instanceof \yii\web\Application) {
            Event::on(Application::class, Application::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
            Event::on(Response::class, Response::EVENT_BEFORE_SEND, [$this, 'beforeSend']);
        }
    }

    /**
     * @param Event $event
     */
    public function beforeRequest(Event $event): void
    {
        $provider = Yii::$container->get(CorrelationIdProviderInterface::class);
        $provider->initialize();
    }

    /**
     * @param Event $event
     */
    public function beforeSend(Event $event): void
    {
        $provider = Yii::$container->get(CorrelationIdProviderInterface::class);
        $logger = Yii::$container->get(RequestResponseLoggerInterface::class);

        if (Yii::$app->response instanceof Response) {
            Yii::$app->response->headers->set('X-Correlation-Id', $provider->get());
        }

        $logger->logRequest(Yii::$app->request);
        $logger->logResponse(Yii::$app->response);
    }
}
