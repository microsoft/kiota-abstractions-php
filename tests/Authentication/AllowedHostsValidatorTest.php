<?php

namespace Microsoft\Kiota\Tests\Authentication;

use Microsoft\Kiota\Abstractions\Authentication\AllowedHostsValidator;
use PHPUnit\Framework\TestCase;

class AllowedHostsValidatorTest extends TestCase
{
    private AllowedHostsValidator $defaultValidator;

    protected function setUp(): void
    {
        $hosts = ["abc.com", "ABC.COM", "abc.com "];
        $this->defaultValidator = new AllowedHostsValidator($hosts);
        parent::setUp();
    }

    public function testConstructorSetsLowercaseTrimmedDeduplicatedHosts(): void
    {
        $expected = ["abc.com"]; //duplicates should not be added to allowed hosts
        $this->assertEquals($expected, $this->defaultValidator->getAllowedHosts());
    }

    public function testSetAllowedHostsSetLowercaseTrimmedDeduplicatedHosts(): void
    {
        $hosts = ["abc.com", "ABC.COM", "abc.com "];
        $validator = new AllowedHostsValidator();
        $validator->setAllowedHosts($hosts);
        $expected = ["abc.com"]; //duplicates should not be added to allowed hosts
        $this->assertEquals($expected, $validator->getAllowedHosts());
    }

    public function testShouldThrowException(): void
    {
        $hosts = ["https://abc.com "];
        $this->expectException(\InvalidArgumentException::class);
        $validator = new AllowedHostsValidator();
        $validator->setAllowedHosts($hosts);
        $expected = ["abc.com"]; //duplicates should not be added to allowed hosts

    }

    public function testIsUrlHostValidWithValidHost(): void
    {
        $this->assertTrue($this->defaultValidator->isUrlHostValid("https://abc.com"));
        $this->assertTrue($this->defaultValidator->isUrlHostValid("HTTPS://ABC.COM"));
        $this->assertTrue($this->defaultValidator->isUrlHostValid("https://abc.com  "));
    }

    public function testIsUrlHostValidWithSubdomainMatchingAllowedSuffix(): void
    {
        $validator = new AllowedHostsValidator([".fabric.microsoft.com"]);
        $this->assertTrue($validator->isUrlHostValid("https://abc.123.graphql.fabric.microsoft.com/path"));
    }

    public function testIsUrlHostValidWithBareDomainAllowedAsSuffixReturnsFalse(): void
    {
        $validator = new AllowedHostsValidator([".fabric.microsoft.com"]);
        $this->assertFalse($validator->isUrlHostValid("https://fabric.microsoft.com/path"));
    }

    public function testSuffixHostMatchingIsCaseInsensitive(): void
    {
        $validator = new AllowedHostsValidator([".Fabric.Microsoft.COM"]);
        $this->assertTrue($validator->isUrlHostValid("https://ABC.z2c.graphql.fabric.microsoft.com/path"));
    }

    public function testAllowsSuffixBasedHostsAfterUpdate(): void
    {
        $validator = new AllowedHostsValidator(["example.com"]);
        $validator->setAllowedHosts([".fabric.microsoft.com"]);
        $this->assertTrue($validator->isUrlHostValid("https://abc.123.graphql.fabric.microsoft.com/path"));
    }

    public function testIsUrlHostValidWithEmptyAllowedHostsReturnsTrue(): void
    {
        $validator = new AllowedHostsValidator();
        $this->assertTrue($validator->isUrlHostValid("https://abc.com"));
    }

    public function testIsUrlHostValidThrowsExceptionWithInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->defaultValidator->isUrlHostValid("http/abc?%#:8080");
    }
}
