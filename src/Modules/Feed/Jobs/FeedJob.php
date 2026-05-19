<?php

declare(strict_types=1);

namespace App\Modules\Feed\Jobs;

use App\Common\Queue\BaseJob;
use yii\queue\Queue;

/**
 * Feed queue job extension point.
 */
final class FeedJob extends BaseJob
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
        if ($queue instanceof Queue) {
            return;
        }
    }
}
