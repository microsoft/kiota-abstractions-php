<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use Microsoft\Kiota\Abstractions\MultiPartBody;
use PHPUnit\Framework\TestCase;

class MultiPartBodyTest extends TestCase
{

    public function testAddOrReplacePart()
    {
    }

    public function testSetRequestAdapter()
    {

    }

    public function testGetPartValue()
    {
        $mpbody = new MultiPartBody();
        $mpbody->addOrReplacePart('hello', 'text/plain', 'Hello world');
        $mpbody->addOrReplacePart('hello2', 'text/plain', 29102);
        $this->assertEquals('Hello world', $mpbody->getPartValue('hello'));
        $this->assertEquals(29102, $mpbody->getPartValue('hello2'));
    }

    public function testRemovePart()
    {
        $mpbody = new MultiPartBody();
        $mpbody->addOrReplacePart('hello', 'text/plain', 'Hello world');
        $mpbody->addOrReplacePart('hello2', 'text/plain', 29102);
        $this->assertEquals('Hello world', $mpbody->getPartValue('hello'));
        $mpbody->removePart('hello');
        $this->assertEquals(null, $mpbody->getPartValue('hello'));
    }

    public function testSerialize()
    {
    }

    public function testGetFieldDeserializers()
    {

    }
}
