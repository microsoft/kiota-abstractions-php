<?php

namespace Microsoft\Kiota\Abstractions;

use Http\Promise\FulfilledPromise;
use Psr\Http\Message\ResponseInterface;
use Http\Promise\Promise;

/**
 * Default response handler that returns the PSR-7 Response
 */
class NativeResponseHandler implements ResponseHandler
{
    /**
     * Returns a promise that resolves to the raw PSR-7 response
     *
     * @param ResponseInterface $response
     * @param arrayarray<string, array{string, string}>|null $errorMappings
     * @return Promise
     */
    public function handleResponseAsync(ResponseInterface $response, ?array $errorMappings = null): Promise
    {
        return new FulfilledPromise($response);
    }
}
