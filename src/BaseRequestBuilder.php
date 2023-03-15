<?php

namespace Microsoft\Kiota\Abstractions;

trait BaseRequestBuilder
{
    /** @var array<string, mixed> $pathParameters  */
    protected array $pathParameters = [];
    protected RequestAdapter $requestAdapter;
    protected string $urlTemplate;
}
