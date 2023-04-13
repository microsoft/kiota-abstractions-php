<?php

namespace Microsoft\Kiota\Abstractions;

abstract class BaseRequestConfiguration
{
    /** @var array<string,array<string>|string> $headers */
    public array $headers = [];
    /** @var array<RequestOption> $options */
    public array $options = [];

    /**
     * @param array<string,string|array<string>> $headers
     * @param array<RequestOption> $options
     */
    public function __construct(array $headers, array $options)
    {
        $this->options = $options;
        $this->headers = $headers;
    }
}
