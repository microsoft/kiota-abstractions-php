<?php

namespace Microsoft\Kiota\Abstractions\Tests\TestFiles;

use Microsoft\Kiota\Abstractions\BaseRequestConfiguration;
use Microsoft\Kiota\Abstractions\RequestOption;

class RequestConfigTestClass
{
    use BaseRequestConfiguration;

    /**
     * @param array<RequestOption> $options
     * @param array<string, array<string>|string> $headers
     */
    public function __construct(array $options, array $headers)
    {
        $this->headers = $headers;
        $this->options = $options;
    }
}
