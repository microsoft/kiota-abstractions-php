<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use InvalidArgumentException;
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

    public function testDefaultBoundaryIsPrintableAndValidHex(): void
    {
        $mpBody = new MultiPartBody();
        $boundary = $mpBody->getBoundary();

        $this->assertNotEmpty($boundary);
        $this->assertEquals(32, strlen($boundary));
        $this->assertTrue(ctype_xdigit($boundary));
        $this->assertTrue(ctype_print($boundary));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9\'()+_,-.\/:=?]+$/', $boundary);
    }

    public function testCustomBoundaryViaConstructor(): void
    {
        $mpBody = new MultiPartBody('custom-boundary-123');
        $this->assertEquals('custom-boundary-123', $mpBody->getBoundary());
    }

    public function testConstructorThrowsOnEmptyBoundary(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MultiPartBody('   ');
    }

    public function testSetBoundary(): void
    {
        $mpBody = new MultiPartBody();
        $mpBody->setBoundary('custom-updated-boundary');
        $this->assertEquals('custom-updated-boundary', $mpBody->getBoundary());
    }

    public function testSetBoundaryThrowsOnEmptyBoundary(): void
    {
        $mpBody = new MultiPartBody();
        $this->expectException(InvalidArgumentException::class);
        $mpBody->setBoundary('');
    }

    public function testAcceptsBoundaryZero(): void
    {
        $mpBody = new MultiPartBody('0');
        $this->assertEquals('0', $mpBody->getBoundary());

        $mpBody2 = new MultiPartBody();
        $mpBody2->setBoundary('0');
        $this->assertEquals('0', $mpBody2->getBoundary());
    }

    public function testAcceptsMaxBoundaryLengthAndRfc2046Symbols(): void
    {
        $maxLenBoundary = str_repeat('a', 70);
        $mpBody = new MultiPartBody($maxLenBoundary);
        $this->assertEquals($maxLenBoundary, $mpBody->getBoundary());

        $symbolsBoundary = "'()+_,-./:=?";
        $mpBody->setBoundary($symbolsBoundary);
        $this->assertEquals($symbolsBoundary, $mpBody->getBoundary());
    }

    public function testConstructorThrowsOnBoundaryWithCrlf(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MultiPartBody("abc\r\ndef");
    }

    public function testConstructorThrowsOnBoundaryWithSpace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MultiPartBody('abc def');
    }

    public function testConstructorThrowsOnBoundaryExceeding70Chars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MultiPartBody(str_repeat('a', 71));
    }

    public function testSetBoundaryThrowsOnInvalidCharacters(): void
    {
        $mpBody = new MultiPartBody();
        $this->expectException(InvalidArgumentException::class);
        $mpBody->setBoundary("boundary\r\ninjection");
    }

    public function testSetBoundaryThrowsOnBoundaryWithSpace(): void
    {
        $mpBody = new MultiPartBody();
        $this->expectException(InvalidArgumentException::class);
        $mpBody->setBoundary('boundary with space');
    }

    public function testSetBoundaryThrowsOnBoundaryExceeding70Chars(): void
    {
        $mpBody = new MultiPartBody();
        $this->expectException(InvalidArgumentException::class);
        $mpBody->setBoundary(str_repeat('x', 71));
    }
}
