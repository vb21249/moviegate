<?php

declare(strict_types=1);

namespace App\Modules\Search\Services;

use App\Common\Services\AbstractService;
use App\Modules\Search\Interfaces\SearchRepositoryInterface;
use App\Modules\Search\Interfaces\SearchServiceModuleInterface;

/**
 * Search application service placeholder.
 */
final class SearchService extends AbstractService implements SearchServiceModuleInterface
{
    public function __construct(
        private readonly SearchRepositoryInterface $repository,
    ) {
    }

    public function execute(string $operation, array $payload = []): array
    {
        return [
            'module' => 'Search',
            'operation' => $operation,
            'payload' => $payload,
            'items' => $this->repository->query($payload),
        ];
    }
}