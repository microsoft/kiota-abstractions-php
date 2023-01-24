<?php
namespace Microsoft\Kiota\Abstractions;

use Http\Promise\Promise;
use Microsoft\Kiota\Abstractions\Serialization\ParseNodeFactory;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriterFactory;
use Microsoft\Kiota\Abstractions\Store\BackingStoreFactory;

/** Service responsible for translating abstract Request Info into concrete native HTTP requests. */
interface RequestAdapter {
    /**
     * Executes the HTTP request specified by the given RequestInformation and returns the deserialized response model.
     * @param RequestInformation $requestInfo the request info to execute.
     * @param array{string, string} $targetCallable the class of the response model to deserialize the response into.
     * @param array<string, array{string, string}>|null $errorMappings
     * @return Promise with the deserialized response model.
     */
    public function sendAsync(
        RequestInformation $requestInfo,
        array $targetCallable,
        ?array $errorMappings = null
    ): Promise;

    /**
     * Gets the serialization writer factory currently in use for the HTTP core service.
     * @return SerializationWriterFactory the serialization writer factory currently in use for the HTTP core service.
     */
    public function getSerializationWriterFactory(): SerializationWriterFactory;

    /**
     * Gets the Parse Node Factory in use
     *
     * @return ParseNodeFactory
     */
    public function getParseNodeFactory(): ParseNodeFactory;

    /**
     * Executes the HTTP request specified by the given RequestInformation and returns the deserialized response model collection.
     * @param RequestInformation $requestInfo the request info to execute.
     * @param array{string, string} $targetCallable the callable representing object creation logic.
     * @param array<string, array{string, string}>|null $errorMappings
     * @return Promise with the deserialized response model collection.
     */
    public function sendCollectionAsync(
        RequestInformation $requestInfo,
        array $targetCallable,
        ?array $errorMappings = null
    ): Promise;

    /**
     * Executes the HTTP request specified by the given RequestInformation and returns the deserialized primitive response model.
     * @param RequestInformation $requestInfo
     * @param string $primitiveType e.g. int, bool
     * @param array<string, array{string, string}>|null $errorMappings
     * @return Promise
     */
    public function sendPrimitiveAsync(
        RequestInformation $requestInfo,
        string $primitiveType,
        ?array $errorMappings = null
    ): Promise;

    /**
     * Executes the HTTP request specified by the given RequestInformation and returns the deserialized primitive response model collection.
     * @param RequestInformation $requestInfo
     * @param string $primitiveType e.g. int, bool
     * @param array<string, array{string, string}>|null $errorMappings
     * @return Promise
     */
    public function sendPrimitiveCollectionAsync(
        RequestInformation $requestInfo,
        string $primitiveType,
        ?array $errorMappings = null
    ): Promise;

    /**
     * Executes the HTTP request specified by the given RequestInformation with no return content.
     * @param RequestInformation $requestInfo
     * @param array<string, array{string, string}>|null $errorMappings
     * @return Promise
     */
    public function sendNoContentAsync(RequestInformation $requestInfo, ?array $errorMappings = null): Promise;
    /**
     * Enables the backing store proxies for the SerializationWriters and ParseNodes in use.
     * @param BackingStoreFactory $backingStoreFactory The backing store factory to use.
     */
    public function enableBackingStore(BackingStoreFactory $backingStoreFactory): void;

    /**
     * Sets The base url for every request.
     * @param string $baseUrl The base url for every request.
     */
    public function setBaseUrl(string $baseUrl): void;

    /**
     * Gets The base url for every request.
     * @return string The base url for every request.
     */
    public function getBaseUrl(): string;

    /**
     * Converts RequestInformation object to an authenticated(containing auth header) PSR-7 Request Object.
     *
     * @param RequestInformation $requestInformation
     * @return Promise
     */
    public function convertToNative(RequestInformation $requestInformation): Promise;
}
