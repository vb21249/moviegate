<?php

declare(strict_types=1);

namespace App\Common\Contracts;

use App\Common\Dto\SoapRequestDto;
use App\Common\Dto\SoapResponseDto;

/**
 * SOAP integration abstraction.
 */
interface SoapClientInterface
{
    /**
     * @param SoapRequestDto $request
     */
    public function call(SoapRequestDto $request): SoapResponseDto;
}
