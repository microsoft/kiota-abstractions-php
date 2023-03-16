<?php

namespace Microsoft\Kiota\Abstractions;

abstract class BaseRequestBuilder
{
    /** @var array<string, mixed> $pathParameters  */
    protected array $pathParameters = [];
    protected RequestAdapter $requestAdapter;
    protected string $urlTemplate;

    /**
     * @param RequestAdapter $requestAdapter
     * @param array<string,mixed> $pathParameters
     * @param string $urlTemplate
     */
    public function __construct(
        RequestAdapter $requestAdapter,
        array $pathParameters,
        string $urlTemplate = ''
    ) {
        $this->requestAdapter = $requestAdapter;
        $this->pathParameters = $pathParameters;
        $this->urlTemplate = $urlTemplate;
    }
}
