<?php

declare(strict_types=1);

namespace App\Modules\Integration\Enums;

/**
 * Integration status enum.
 */
enum IntegrationStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}