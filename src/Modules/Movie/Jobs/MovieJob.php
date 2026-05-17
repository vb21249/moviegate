<?php

declare(strict_types=1);

namespace App\Modules\Movie\Jobs;

use App\Common\Queue\BaseJob;
use yii\queue\Queue;

/**
 * Movie queue job placeholder.
 */
final class MovieJob extends BaseJob
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