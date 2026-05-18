<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

use App\Common\Requests\BaseRequest;

/**
 * Base auth request model with helper methods for readable validation errors.
 */
abstract class AuthRequest extends BaseRequest
{
    /**
     * @return string
     */
    public function firstErrorMessage(): string
    {
        $firstErrors = $this->getFirstErrors();

        return $firstErrors === [] ? 'Validation failed.' : (string) reset($firstErrors);
    }
}
