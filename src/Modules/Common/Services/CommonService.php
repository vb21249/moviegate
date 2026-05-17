<?php

declare(strict_types=1);

namespace App\Modules\Common\Services;

use App\Common\Services\AbstractService;
use App\Modules\Common\Interfaces\CommonRepositoryInterface;
use App\Modules\Common\Interfaces\CommonServiceInterface;

/**
 * Common application service placeholder.
 */
final class CommonService extends AbstractService implements CommonServiceInterface
{
    public function __construct(
        private readonly CommonRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Common',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}