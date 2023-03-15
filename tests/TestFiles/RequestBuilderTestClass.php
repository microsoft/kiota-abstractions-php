<?php

namespace Microsoft\Kiota\Abstractions\Tests\TestFiles;

use Microsoft\Kiota\Abstractions\BaseRequestBuilder;
use Microsoft\Kiota\Abstractions\RequestAdapter;

class RequestBuilderTestClass extends BaseRequestBuilder
{

    /**
     * @param RequestAdapter $requestAdapter
     * @param array<string,mixed> $pathParameters
     */
    public function __construct(RequestAdapter $requestAdapter, array $pathParameters)
    {
        $this->pathParameters = $pathParameters;
        $this->requestAdapter = $requestAdapter;
        $this->urlTemplate = "{+baseUrl}";
    }
}
