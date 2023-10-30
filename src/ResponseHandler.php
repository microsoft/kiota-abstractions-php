<?php
namespace Microsoft\Kiota\Abstractions;

use Http\Promise\Promise;
use Psr\Http\Message\ResponseInterface;
use Microsoft\Kiota\Abstractions\Serialization\Parsable;

interface ResponseHandler {
    /**
     * Callback method that is invoked when a response is received.
     * @template T of Parsable
     * @param ResponseInterface $response The native response object.
     * @param array<string, array{class-string<T>, string}>|null $errorMappings
     * map of error status codes to  exception models to deserialize to
     * @return Promise<mixed> A Promise that contains the deserialized response.
     */
    public function handleResponseAsync(ResponseInterface $response, ?array $errorMappings = null): Promise;
}
