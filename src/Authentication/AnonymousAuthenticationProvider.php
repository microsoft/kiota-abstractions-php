<?php

namespace Microsoft\Kiota\Abstractions\Authentication;

use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Microsoft\Kiota\Abstractions\RequestInformation;

class AnonymousAuthenticationProvider implements AuthenticationProvider {

    /**
     * @param RequestInformation $request Request information
     * @param array $additionalAuthenticationContext
     * @return Promise
     */
    public function authenticateRequest(RequestInformation $request, array $additionalAuthenticationContext = []): Promise {
        return new FulfilledPromise(null);
    }
}
