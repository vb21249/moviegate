<?php

declare(strict_types=1);

namespace App\Common\Jobs;

use App\Common\Queue\BaseJob;

/**
 * Base email sending job placeholder.
 */
final class SendEmailJob extends BaseJob
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
