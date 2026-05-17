<?php

declare(strict_types=1);

namespace App\Common\Logging;

use App\Common\Contracts\CorrelationIdProviderInterface;
use Ramsey\Uuid\Uuid;
use Yii;

/**
 * Provides request correlation IDs.
 */
final class CorrelationIdProvider implements CorrelationIdProviderInterface
{
    private ?string $correlationId = null;

    public function initialize(): void
    {
        $headerValue = Yii::$app?->request?->headers->get('X-Correlation-Id');
        $this->correlationId = $headerValue ?: Uuid::uuid7()->toString();
    }

    public function get(): string
    {
        if ($this->correlationId === null) {
            $this->initialize();
        }

        return $this->correlationId;
    }
}
