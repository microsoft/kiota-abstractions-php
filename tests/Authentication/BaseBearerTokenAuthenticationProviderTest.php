<?php

namespace Microsoft\Kiota\Abstractions\Tests\Authentication;

use Http\Promise\FulfilledPromise;
use Microsoft\Kiota\Abstractions\Authentication\AccessTokenProvider;
use Microsoft\Kiota\Abstractions\Authentication\BaseBearerTokenAuthenticationProvider;
use Microsoft\Kiota\Abstractions\RequestInformation;
use PHPUnit\Framework\TestCase;

class BaseBearerTokenAuthenticationProviderTest extends TestCase
{
    private AccessTokenProvider $accessTokenProvider;
    private BaseBearerTokenAuthenticationProvider $baseBearerTokenAuthenticationProvider;
    private RequestInformation $requestInformation;

    protected function setUp(): void
    {
        $this->accessTokenProvider = $this->createStub(AccessTokenProvider::class);
        $this->accessTokenProvider->method('getAuthorizationTokenAsync')->willReturn(new FulfilledPromise('abc'));
        $this->baseBearerTokenAuthenticationProvider = new BaseBearerTokenAuthenticationProvider($this->accessTokenProvider);
        $this->requestInformation = new RequestInformation();
        $this->requestInformation->urlTemplate = '{+baseurl}';
        $this->requestInformation->pathParameters['baseurl'] = 'https://graph.microsoft.com';
    }

    public function testAuthenticateRequestAddsTokenToHeader(): void
    {
        $this->baseBearerTokenAuthenticationProvider->authenticateRequest($this->requestInformation)->wait();
        $this->assertTrue($this->requestInformation->getHeaders()->contains('Authorization'));
        $this->assertEquals(['Bearer abc'], $this->requestInformation->getHeaders()->get('Authorization'));
    }

    public function testAuthenticateRequestRemovesHeaderWithClaims(): void
    {
        $this->requestInformation->getHeaders()->add('Authorization', 'Bearer 123');
        $this->baseBearerTokenAuthenticationProvider->authenticateRequest($this->requestInformation, ['claims' => 'eyJhY2Nlc3NfdG9rZW4iOnsiYWNycyI6eyJlc3NlbnRpYWwiOnRydWUsInZhbHVlIjoiYzEifX19'])->wait();
        $this->assertTrue($this->requestInformation->getHeaders()->contains('Authorization'));
        $this->assertEquals(['Bearer abc'], $this->requestInformation->getHeaders()->get('Authorization'));
    }


}
