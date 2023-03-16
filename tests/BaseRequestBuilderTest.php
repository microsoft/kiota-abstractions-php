<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use Microsoft\Kiota\Abstractions\BaseRequestBuilder;
use Microsoft\Kiota\Abstractions\Tests\TestFiles\RequestBuilderTestClass;
use Microsoft\Kiota\Abstractions\Tests\TestFiles\TestRequestAdapter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BaseRequestBuilderTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testPropertiesCorrectlySet(): void
    {
        $mock = $this->getMockBuilder(BaseRequestBuilder::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $requestAdapter = new TestRequestAdapter();
        $reflectedClass = new ReflectionClass(BaseRequestBuilder::class);

        $constructor = $reflectedClass->getConstructor();
        $this->assertNotNull($constructor);
        $constructor->invoke($mock, $requestAdapter, ["id" => 1]);
        $props = $reflectedClass->getProperties();
        $this->assertCount(3, $props);
        $this->assertEquals('pathParameters', $props[0]->name);
        $this->assertEquals('requestAdapter', $props[1]->name);
        $this->assertEquals('urlTemplate', $props[2]->name);
    }
}
