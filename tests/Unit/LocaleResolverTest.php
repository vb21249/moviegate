<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Common\Localization\RequestLocaleResolver;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\console\Application;

final class LocaleResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        new Application([
            'id' => 'locale-resolver-test',
            'basePath' => dirname(__DIR__, 2),
            'params' => [
                'i18n' => [
                    'defaultLanguage' => 'en-US',
                    'supportedLanguages' => ['en-US', 'uk-UA', 'ru-RU'],
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        if (Yii::$app !== null && Yii::$app->has('errorHandler')) {
            Yii::$app->getErrorHandler()->unregister();
        }

        Yii::$app = null;

        parent::tearDown();
    }

    public function testResolvesExactSupportedLanguage(): void
    {
        self::assertSame('uk-UA', (new RequestLocaleResolver())->resolve('uk-UA'));
    }

    public function testResolvesPrimaryLanguageToSupportedRegion(): void
    {
        self::assertSame('ru-RU', (new RequestLocaleResolver())->resolve('ru'));
    }

    public function testFallsBackToDefaultForUnsupportedLanguage(): void
    {
        self::assertSame('en-US', (new RequestLocaleResolver())->resolve('de-DE'));
    }
}
