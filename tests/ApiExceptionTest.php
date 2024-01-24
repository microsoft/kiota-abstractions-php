<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use Microsoft\Kiota\Abstractions\ApiException;
use PHPUnit\Framework\TestCase;

class ApiExceptionTest extends TestCase
{
    public function testCanSetFieldsAndGet(): void
    {
        $apiException = new ApiException("Malformed input.", 400, null);
        $this->assertEquals(400, $apiException->getCode());
        $this->assertEquals("Malformed input.", $apiException->getMessage());

        $apiException->setResponseStatusCode(400);
        $apiException->setResponseHeaders(['Content-Type' => ['application/json'], 'Content-Length' => ['200']]);

        $this->assertEquals(400, $apiException->getResponseStatusCode());
        $this->assertEquals(['Content-Type' => ['application/json'], 'Content-Length' => ['200']], $apiException->getResponseHeaders());
    }
}
