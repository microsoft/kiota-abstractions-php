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
     * @var ResponseInterface|null
     */
    private ?ResponseInterface $nativeResponse = null;

    /**
     * Returns the PSR-7 Response
     *
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->nativeResponse;
    }

    /**
     * Returns a promise that resolves to the raw PSR-7 response
     *
     * @param ResponseInterface $response
     * @param array<string, array{string, string}>|null $errorMappings
     * @return Promise<null>
     */
    public function handleResponseAsync(ResponseInterface $response, ?array $errorMappings = null): Promise
    {
        $this->nativeResponse = $response;
        return new FulfilledPromise(null);
    }
}
