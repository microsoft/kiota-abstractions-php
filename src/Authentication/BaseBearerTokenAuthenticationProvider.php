<?php

namespace Microsoft\Kiota\Abstractions\Authentication;

use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Microsoft\Kiota\Abstractions\RequestInformation;

/**
 * Class BaseBearerTokenAuthenticationProvider
 *
 * Provides a base class for implementing {@link AuthenticationProvider} for Bearer authentication scheme
 *
 * @package Microsoft\Kiota\Abstractions\Authentication
 * @copyright 2022 Microsoft Corporation
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://developer.microsoft.com/graph
 */
class BaseBearerTokenAuthenticationProvider implements AuthenticationProvider {

    /**
     * @var string $authorizationHeaderKey The Authorization header key
     */
    private static string $authorizationHeaderKey = "Authorization";

    /**
     * Claims key to search for in $additionalAuthenticationContext
     *
     * @var string
     */
    private static string $claimsKey = "claims";

    /**
     * @var AccessTokenProvider {@link AccessTokenProvider}
     */
    private AccessTokenProvider $accessTokenProvider;

    /**
     * Creates a new instance
     * @param AccessTokenProvider $accessTokenProvider to use for getting the access token
     */
    public function __construct(AccessTokenProvider $accessTokenProvider)
    {
        $this->accessTokenProvider = $accessTokenProvider;
    }

    /**
     * Gets the {@link AccessTokenProvider} used for getting the access token
     *
     * @return AccessTokenProvider
     */
    public function getAccessTokenProvider(): AccessTokenProvider
    {
        return $this->accessTokenProvider;
    }

    /**
     * @param RequestInformation $request
     * @param array<string, mixed> $additionalAuthenticationContext
     * @return Promise<RequestInformation>
     */
    public function authenticateRequest(
        RequestInformation $request,
        array $additionalAuthenticationContext = []
    ): Promise
    {
        if (array_key_exists(self::$claimsKey, $additionalAuthenticationContext)
            && $request->getHeaders()->contains(self::$authorizationHeaderKey)
        ) {
            $request->getHeaders()->remove(self::$authorizationHeaderKey);
        }

        if (!$request->getHeaders()->contains(self::$authorizationHeaderKey)) {
            return $this->getAccessTokenProvider()
                        ->getAuthorizationTokenAsync($request->getUri(), $additionalAuthenticationContext)
                        ->then(function ($token) use ($request) {
                            if ($token) {
                                $request->addHeader(self::$authorizationHeaderKey, "Bearer {$token}");
                            }
                            return $request;
                        });
        }
        return new FulfilledPromise($request);
    }
}
