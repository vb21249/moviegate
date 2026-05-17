<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Jobs;

use App\Common\Queue\BaseJob;
use yii\queue\Queue;

/**
 * Recommendation queue job placeholder.
 */
final class RecommendationJob extends BaseJob
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