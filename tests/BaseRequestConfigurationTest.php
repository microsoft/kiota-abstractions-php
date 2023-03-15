<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use Microsoft\Kiota\Abstractions\Tests\TestFiles\RequestConfigTestClass;
use PHPUnit\Framework\TestCase;

class BaseRequestConfigurationTest extends TestCase
{
    public function testPropertiesCorrectlySet(): void
    {
        $config = new RequestConfigTestClass([], ["Content-Type" => ['application/json']]);
        $ref = new \ReflectionClass($config);
        $props = $ref->getProperties();
        $this->assertCount(2, $props);
        $this->assertEquals('headers', $props[0]->name);
        $this->assertEquals('options', $props[1]->name);
    }
}
