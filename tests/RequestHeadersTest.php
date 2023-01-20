<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use Microsoft\Kiota\Abstractions\RequestHeaders;
use PHPUnit\Framework\TestCase;

class RequestHeadersTest extends TestCase
{
    private const APPLICATION_JSON = 'application/json';
    private const APPLICATION_XML = 'application/xml';
    public function testCanGetValues(): void
    {
        $rq = new RequestHeaders();
        $rq->add('Content-Type', self::APPLICATION_JSON);
        $this->assertEquals([self::APPLICATION_JSON], $rq->get('Content-Type'));
    }

    public function testGetAll(): void
    {
        $requestHeaders = new RequestHeaders();
        $requestHeaders->addAll('Content-Type', [self::APPLICATION_JSON, self::APPLICATION_XML]);
        $this->assertCount(2, $requestHeaders->get('Content-Type'));
        $this->assertCount(1, $requestHeaders->getAll());
    }

    public function testCanClear(): void
    {
        $requestHeaders = new RequestHeaders();
        $requestHeaders->addAll('Content-Type', [self::APPLICATION_JSON, self::APPLICATION_XML]);
        $requestHeaders->clear();
        $this->assertEquals(0, $requestHeaders->count());
        $requestHeaders->add('User-Agent', 'MyUserAgent');
        $this->assertCount(1, $requestHeaders->get('User-Agent'));
    }

    public function testGetHeaderNames(): void
    {
        $headers = new RequestHeaders();
        $headers->add('Content-Type', self::APPLICATION_JSON);
        $headers->add('User-Agent', 'Browser');
        $expected = array_map('strtolower',['Content-Type', 'User-Agent']);
        $this->assertEquals($expected, $headers->getHeaderNames());
    }

    public function testCanRemove(): void
    {
        $headers = RequestHeaders::from(
            [
                'Content-Type' => [self::APPLICATION_JSON, self::APPLICATION_XML],
                'User-Agent' => ['Moz']
            ]
        );

        $this->assertCount(2, $headers);
        $headers->remove('User-Agent');
        $this->assertEquals(1, $headers->count());
        $this->assertCount(2, $headers->get('Content-Type'));
        $this->assertCount(0, $headers->get('User-Agent'));
    }

    public function testContains(): void
    {
        $headers = new RequestHeaders();
        $this->assertFalse($headers->contains('User-Agent'));
        $headers->add('User-Agent', 'Mozilla');
        $this->assertTrue($headers->contains('User-Agent'));
    }
}
