<?php

declare(strict_types=1);

namespace App\Modules\Integration\Clients;

use App\Common\Contracts\SoapClientInterface;
use App\Common\Dto\SoapRequestDto;

/**
 * SOAP gateway adapter.
 */
final class LegacySoapGateway implements LegacySoapGatewayInterface
{
    public function __construct(
        private readonly SoapClientInterface $soapClient,
    ) {
    }

    public function call(string $operation, array $payload = []): array
    {
        $response = $this->soapClient->call(new SoapRequestDto(
            operation: $operation,
            payload: $payload
        ));

        return (array) $response->body;
    }
}
