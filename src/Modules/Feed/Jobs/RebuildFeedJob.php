<?php

declare(strict_types=1);

namespace App\Modules\Feed\Jobs;

use App\Common\Queue\BaseJob;

/**
 * Feed rebuild job placeholder.
 */
final class RebuildFeedJob extends BaseJob
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
