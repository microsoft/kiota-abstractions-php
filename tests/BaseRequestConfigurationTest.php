<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use Microsoft\Kiota\Abstractions\BaseRequestConfiguration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class BaseRequestConfigurationTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testPropertiesCorrectlySet(): void
    {
        $mock = $this->getMockBuilder(BaseRequestConfiguration::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $reflectedClass = new ReflectionClass(BaseRequestConfiguration::class);

        $constructor = $reflectedClass->getConstructor();
        $this->assertNotNull($constructor);
        $constructor->invoke($mock, [], ['Content-Type' => ['application/json']]);
        $props = $reflectedClass->getProperties();
        $this->assertCount(2, $props);
        $this->assertEquals('headers', $props[0]->name);
        $this->assertEquals('options', $props[1]->name);
    }
}
