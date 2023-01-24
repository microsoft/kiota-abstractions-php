<?php
namespace Microsoft\Kiota\Abstractions;

use Http\Promise\Promise;
use Psr\Http\Message\ResponseInterface;

interface ResponseHandler {
    /**
     * Callback method that is invoked when a response is received.
     * @param ResponseInterface $response The native response object.
     * @param array<string, array<string, string>>|null $errorMappings map of error status codes to  exception models to deserialize to
     * @return Promise A Promise that represents the asynchronous operation and contains the deserialized response.
     */
    public function handleResponseAsync(ResponseInterface $response, ?array $errorMappings = null): Promise;
}
