<?php

namespace Microsoft\Kiota\Abstractions;

trait BaseRequestConfiguration
{
    /** @var array<string,array<string>|string> $headers */
    protected array $headers = [];
    /** @var array<RequestOption> $options */
    protected array $options = [];
}
