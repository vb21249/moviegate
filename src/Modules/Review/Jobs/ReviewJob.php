<?php

declare(strict_types=1);

namespace App\Modules\Review\Jobs;

use App\Common\Queue\BaseJob;
use yii\queue\Queue;

/**
 * Review queue job placeholder.
 */
final class ReviewJob extends BaseJob
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