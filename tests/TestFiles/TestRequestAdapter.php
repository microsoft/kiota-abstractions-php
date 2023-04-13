<?php

namespace Microsoft\Kiota\Abstractions\Tests\TestFiles;

use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Microsoft\Kiota\Abstractions\RequestAdapter;
use Microsoft\Kiota\Abstractions\RequestInformation;
use Microsoft\Kiota\Abstractions\Serialization\ParseNodeFactory;
use Microsoft\Kiota\Abstractions\Serialization\ParseNodeFactoryRegistry;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriterFactory;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriterFactoryRegistry;
use Microsoft\Kiota\Abstractions\Store\BackingStore;
use Microsoft\Kiota\Abstractions\Store\BackingStoreFactory;

class TestRequestAdapter implements RequestAdapter
{

    private string $baseUrl = '';
    protected BackingStore $backingStore;

    public function sendAsync(RequestInformation $requestInfo, array $targetCallable, ?array $errorMappings = null): Promise
    {
        return new FulfilledPromise(null);
    }

    public function getSerializationWriterFactory(): SerializationWriterFactory
    {
        return SerializationWriterFactoryRegistry::getDefaultInstance();
    }

    public function getParseNodeFactory(): ParseNodeFactory
    {
        return ParseNodeFactoryRegistry::getDefaultInstance();
    }

    public function sendCollectionAsync(RequestInformation $requestInfo, array $targetCallable, ?array $errorMappings = null): Promise
    {
        return new FulfilledPromise(null);
    }

    public function sendPrimitiveAsync(RequestInformation $requestInfo, string $primitiveType, ?array $errorMappings = null): Promise
    {
        return new FulfilledPromise(null);
    }

    public function sendPrimitiveCollectionAsync(RequestInformation $requestInfo, string $primitiveType, ?array $errorMappings = null): Promise
    {
        return new FulfilledPromise(null);
    }

    public function sendNoContentAsync(RequestInformation $requestInfo, ?array $errorMappings = null): Promise
    {
        return new FulfilledPromise(null);
    }

    public function enableBackingStore(BackingStoreFactory $backingStoreFactory): void
    {
        $this->backingStore = $backingStoreFactory->createBackingStore();
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function convertToNative(RequestInformation $requestInformation): Promise
    {
        return new FulfilledPromise(null);
    }
}
