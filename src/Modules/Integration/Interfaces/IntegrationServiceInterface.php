<?php

declare(strict_types=1);

namespace App\Modules\Integration\Interfaces;

use App\Modules\Integration\Requests\IntegrationRequest;
use App\Modules\Integration\Responses\IntegrationResponse;

/**
 * Integration application service contract.
 */
interface IntegrationServiceInterface
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function execute(string $operation, array $payload = []): array;

    /**
     * @param IntegrationRequest $request
     *
     * @return IntegrationResponse
     */
    public function syncTmdb(IntegrationRequest $request): IntegrationResponse;
}
