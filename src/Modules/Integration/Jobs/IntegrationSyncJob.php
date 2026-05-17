<?php

declare(strict_types=1);

namespace App\Modules\Integration\Jobs;

use App\Common\Queue\BaseJob;

/**
 * External integration sync job placeholder.
 */
final class IntegrationSyncJob extends BaseJob
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
        array $config = [],
    ) {
        parent::__construct($config);
    }

    public function execute($queue): void
    {
    }
}
