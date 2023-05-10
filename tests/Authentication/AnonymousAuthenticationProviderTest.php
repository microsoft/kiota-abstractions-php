<?php

namespace Microsoft\Kiota\Abstractions\Tests\Authentication;

use Microsoft\Kiota\Abstractions\Authentication\AnonymousAuthenticationProvider;
use Microsoft\Kiota\Abstractions\RequestInformation;
use PHPUnit\Framework\TestCase;

class AnonymousAuthenticationProviderTest extends TestCase
{
    public function testRequestNotManipulated(): void
    {
        $auth = new AnonymousAuthenticationProvider();
        $request = new RequestInformation();
        $request->urlTemplate = '{+baseurl}';
        $request->pathParameters['baseurl'] = 'https://graph.microsoft.com';

        $auth->authenticateRequest($request)->wait();
        $this->assertEmpty($request->getHeaders()->get('Authorization'));

        $auth->authenticateRequest($request, ['claims' => 'abc'])->wait();
        $this->assertEmpty($request->getHeaders()->get('Authorization'));
    }
}
