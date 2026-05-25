<?php

declare(strict_types=1);

namespace App\Common\Exceptions;

/**
 * Runtime lock file is already locked by another process.
 */
final class FileLockUnavailableException extends RuntimeFileException
{
}
