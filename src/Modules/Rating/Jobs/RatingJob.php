<?php

declare(strict_types=1);

namespace App\Modules\Rating\Jobs;

use App\Common\Queue\BaseJob;
use yii\queue\Queue;

/**
 * Rating queue job placeholder.
 */
final class RatingJob extends BaseJob
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