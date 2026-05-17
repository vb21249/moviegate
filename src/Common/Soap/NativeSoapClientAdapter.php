<?php

declare(strict_types=1);

namespace App\Common\Soap;

use App\Common\Contracts\SoapClientInterface;
use App\Common\Dto\SoapRequestDto;
use App\Common\Dto\SoapResponseDto;
use SoapClient;
use Yii;

/**
 * Native SOAP client adapter.
 */
final class NativeSoapClientAdapter implements SoapClientInterface
{
    public function call(SoapRequestDto $request): SoapResponseDto
    {
        $client = new SoapClient((string) Yii::$app->params['soap']['endpoint'], [
            'login' => Yii::$app->params['soap']['login'],
            'password' => Yii::$app->params['soap']['password'],
            'trace' => true,
            ...$request->options,
        ]);

        return new SoapResponseDto(
            body: $client->__soapCall($request->operation, [$request->payload])
        );
    }
}
