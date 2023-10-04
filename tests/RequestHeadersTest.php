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

    public function testGetNonExistentKey(): void
    {
        $this->assertEmpty((new RequestHeaders)->get("abc"));
    }

    public function testGetAll(): void
    {
        $requestHeaders = new RequestHeaders();
        $requestHeaders->putAllToKey('Content-Type', [self::APPLICATION_JSON, self::APPLICATION_XML]);
        $this->assertCount(2, $requestHeaders->get('Content-Type'));
        $this->assertCount(1, $requestHeaders->getAll());
    }

    public function testCanClear(): void
    {
        $requestHeaders = new RequestHeaders();
        $requestHeaders->putAllToKey('Content-Type', [self::APPLICATION_JSON, self::APPLICATION_XML]);
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
        $expected = array_map('strtolower', ['Content-Type', 'User-Agent']);
        $this->assertEquals($expected, $headers->getHeaderNames());
    }

    public function testCanRemove(): void
    {
        $headers = new RequestHeaders();
        $headers->putAll(
            [
                'Content-Type' => [self::APPLICATION_JSON, self::APPLICATION_XML],
                'User-Agent' => ['Moz']
            ]
        );

        $this->assertEquals(2, $headers->count());
        $headers->remove('User-Agent');
        $this->assertEquals(1, $headers->count());
        $this->assertCount(2, $headers->get('Content-Type'));
        $this->assertEmpty($headers->get('User-Agent'));
    }

    public function testContains(): void
    {
        $headers = new RequestHeaders();
        $this->assertFalse($headers->contains('User-Agent'));
        $headers->add('User-Agent', 'Mozilla');
        $this->assertTrue($headers->contains('User-Agent'));
    }

    public function testCanAdd(): void
    {
        $headers = new RequestHeaders();
        $key = "key";
        $headers->add($key, "value");
        $this->assertEquals(["value"], $headers->get($key));

        $headers->add($key, "value2");
        $this->assertEquals(["value", "value2"], $headers->get($key));

        // case sensitive
        $headers->add($key, "VALUE2");
        $this->assertEquals(["value", "value2", "VALUE2"], $headers->get($key));
    }

    public function testCanTryAdd(): void
    {
        $headers = new RequestHeaders();
        $key = "key";
        $this->assertTrue($headers->tryAdd($key, "value"));
        $this->assertEquals(["value"], $headers->get($key));

        $this->assertFalse($headers->tryAdd($key, "value2"));
        $this->assertEquals(["value"], $headers->get($key));
    }

    public function testCanPutAll(): void
    {
        $headers = new RequestHeaders();
        $headers->putAll([
            "key1" => "value1",
            "key2" => ["value2", "value3"]
        ]);
        $this->assertEquals(["value1"], $headers->get("key1"));
        $this->assertEquals(["value2", "value3"], $headers->get("key2"));
    }
}
