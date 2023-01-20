<?php

namespace Microsoft\Kiota\Abstractions\Tests;

use Microsoft\Kiota\Abstractions\RequestHeaders;
use PHPUnit\Framework\TestCase;

class RequestHeadersTest extends TestCase
{
    public function testCanGetValues(): void {
        $rq = new RequestHeaders();
        $rq->add('Content-Type', 'application/json');
        $this->assertEquals(['application/json'], $rq->get('Content-Type'));
    }

    public function testGetAll(): void {
        $requestHeaders = new RequestHeaders();
        $requestHeaders->addAll('Content-Type', ['application/json', 'application/xml']);
        $this->assertCount(2, $requestHeaders->get('Content-Type'));
    }

    public function testCanClear(): void {
        $requestHeaders = new RequestHeaders();
        $requestHeaders->addAll('Content-Type', ['application/json', 'application/xml']);
        $requestHeaders->clear();
        $this->assertEquals(0, $requestHeaders->count());
        $requestHeaders->add('User-Agent', 'MyUserAgent');
        $this->assertCount(1, $requestHeaders->get('User-Agent'));
    }

    public function testGetHeaderNames(): void {
        $headers = new RequestHeaders();
        $headers->add('Content-Type', 'application/json');
        $headers->add('User-Agent', 'Browser');
        $expected = array_map('strtolower',['Content-Type', 'User-Agent']);
        $this->assertEquals($expected, $headers->getHeaderNames());
    }

    public function testCanRemove(): void {
        $headers = RequestHeaders::from(
            [
                'Content-Type' => ['application/json', 'application/xml'],
                'User-Agent' => ['Moz']
            ]
        );

        $this->assertCount(2, $headers);
        $headers->remove('User-Agent');
        $this->assertEquals(1, $headers->count());
        $this->assertCount(2, $headers->get('Content-Type'));
        $this->assertCount(0, $headers->get('User-Agent'));
    }
}
