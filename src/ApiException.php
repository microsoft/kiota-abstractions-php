<?php

namespace Microsoft\Kiota\Abstractions;
use Exception;

class ApiException extends Exception
{
    /**
     * HTTP response status code
     *
     * @var int|null
     */
    private ?int $responseStatusCode = null;

    /** @var array<string, string[]> */
    private array $responseHeaders = [];

    /**
     * @param string $message
     * @param int $code
     * @param Exception|null $innerException
     */
    public function __construct(string $message = "", int $code = 0, ?Exception $innerException = null) {
        parent::__construct($message, $code, $innerException);
    }

    /**
     * Set HTTP response status code from API
     *
     * @param int $statusCode
     * @return void
     */
    public function setResponseStatusCode(int $statusCode): void
    {
        $this->responseStatusCode = $statusCode;
    }

    /**
     * Return HTTP response status code from API
     *
     * @return int|null
     */
    public function getResponseStatusCode(): ?int
    {
        return $this->responseStatusCode;
    }

    /**
     * @param array<string, string[]> $responseHeaders
     */
    public function setResponseHeaders(array $responseHeaders): void
    {
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * @return array<string, string[]>
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }
}
