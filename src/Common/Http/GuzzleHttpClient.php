<?php

declare(strict_types=1);

namespace App\Common\Http;

use App\Common\Contracts\HttpClientInterface;
use App\Common\Dto\HttpRequestDto;
use App\Common\Dto\HttpResponseDto;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Utils;

/**
 * Guzzle based HTTP adapter.
 */
final class GuzzleHttpClient implements HttpClientInterface
{
    /**
     * @param ClientInterface $client
     */
    public function __construct(
        private readonly ClientInterface $client = new \GuzzleHttp\Client(),
    ) {
    }

    /**
     * @param HttpRequestDto $request
     */
    public function send(HttpRequestDto $request): HttpResponseDto
    {
        $response = $this->client->request($request->method, $request->uri, [
            'headers' => $request->headers,
            'query' => $request->query,
            'body' => $request->body !== null ? Utils::streamFor(json_encode($request->body, JSON_THROW_ON_ERROR)) : null,
        ]);

        return new HttpResponseDto(
            statusCode: $response->getStatusCode(),
            headers: array_map(
                static fn (array $values): string => implode(',', $values),
                $response->getHeaders()
            ),
            body: json_decode((string) $response->getBody(), true)
        );
    }
}
