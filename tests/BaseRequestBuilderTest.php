<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use Microsoft\Kiota\Abstractions\Tests\TestFiles\RequestBuilderTestClass;
use Microsoft\Kiota\Abstractions\Tests\TestFiles\TestRequestAdapter;
use PHPUnit\Framework\TestCase;

class BaseRequestBuilderTest extends TestCase
{
    public function testPropertiesCorrectlySet(): void
    {
        $requestAdapter = new TestRequestAdapter();
        $requestBuilder = new RequestBuilderTestClass($requestAdapter, ["id" => 1]);
        $ref = new \ReflectionClass($requestBuilder);
        $props = $ref->getProperties();
        $this->assertCount(3, $props);
        $this->assertEquals('pathParameters', $props[0]->name);
        $this->assertEquals('requestAdapter', $props[1]->name);
        $this->assertEquals('urlTemplate', $props[2]->name);
    }
}
