<?php

declare(strict_types=1);

namespace App\Modules\Notification\Jobs;

use App\Common\Queue\BaseJob;

/**
 * Notification dispatch job extension point.
 */
final class SendNotificationJob extends BaseJob
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
