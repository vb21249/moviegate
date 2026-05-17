<?php

declare(strict_types=1);

namespace App\Common\Contracts;

use App\Common\Dto\HttpRequestDto;
use App\Common\Dto\HttpResponseDto;

/**
 * Abstraction over outbound REST clients.
 */
interface HttpClientInterface
{
    /**
     * @param HttpRequestDto $request
     */
    public function send(HttpRequestDto $request): HttpResponseDto;
}
