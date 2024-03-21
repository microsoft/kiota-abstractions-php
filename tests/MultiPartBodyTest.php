<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use Microsoft\Kiota\Abstractions\MultiPartBody;
use PHPUnit\Framework\TestCase;

class MultiPartBodyTest extends TestCase
{

    public function testAddOrReplacePart(): void
    {
        $mpBody = new MultiPartBody();
        $mpBody->addOrReplacePart('hello', 'text/plain', 'Hello world');
        $this->assertEquals('Hello world', $mpBody->getPartValue('hello'));
        $mpBody->addOrReplacePart('hello', 'text/plain', 'Second Hello World');
        $this->assertEquals('Second Hello World', $mpBody->getPartValue('hello'));
    }

    public function testGetPartValue(): void
    {
        $mpBody = new MultiPartBody();
        $mpBody->addOrReplacePart('hello', 'text/plain', 'Hello world');
        $mpBody->addOrReplacePart('hello2', 'text/plain', 29102);
        $this->assertEquals('Hello world', $mpBody->getPartValue('hello'));
        $this->assertEquals(29102, $mpBody->getPartValue('hello2'));
    }

    public function testRemovePart(): void
    {
        $mpBody = new MultiPartBody();
        $mpBody->addOrReplacePart('hello', 'text/plain', 'Hello world');
        $mpBody->addOrReplacePart('hello2', 'text/plain', 29102);
        $this->assertEquals('Hello world', $mpBody->getPartValue('hello'));
        $mpBody->removePart('hello');
        $this->assertNull($mpBody->getPartValue('hello'));
    }
    public function testGetFieldDeserializers(): void
    {
        $mpBody = new MultiPartBody();
        $this->assertEmpty($mpBody->getFieldDeserializers());
    }
}
