<?php

declare(strict_types=1);

namespace App\Modules\Integration\Services;

use App\Common\Services\AbstractService;
use App\Modules\Integration\Interfaces\IntegrationRepositoryInterface;
use App\Modules\Integration\Interfaces\IntegrationServiceInterface;

/**
 * Integration application service placeholder.
 */
final class IntegrationService extends AbstractService implements IntegrationServiceInterface
{
    public function __construct(
        private readonly IntegrationRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Integration',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}