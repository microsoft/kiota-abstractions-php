<?php

namespace Microsoft\Kiota\Abstractions\Authentication;

use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Microsoft\Kiota\Abstractions\RequestInformation;

class AnonymousAuthenticationProvider implements AuthenticationProvider {

    /**
     * @param RequestInformation $request Request information
     * @param array<string, mixed> $additionalAuthenticationContext
     * @return Promise<RequestInformation>
     */
    public function authenticateRequest(
        RequestInformation $request,
        array $additionalAuthenticationContext = []
    ): Promise
    {
        return new FulfilledPromise($request);
    }
}
