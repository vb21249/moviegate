<?php

declare(strict_types=1);

namespace App\Modules\Auth\Jobs;

use App\Common\Queue\BaseJob;
use yii\queue\Queue;

/**
 * Auth queue job placeholder.
 */
final class AuthJob extends BaseJob
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