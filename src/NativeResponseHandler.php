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
    public function handleResponseAsync(ResponseInterface $response): Promise
    {
        return new FulfilledPromise($response);
    }
}
