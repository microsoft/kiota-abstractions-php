<?php

namespace Microsoft\Kiota\Abstractions;
use Exception;
use Psr\Http\Message\ResponseInterface;

class ApiException extends Exception
{
    /**
     * Raw response object
     *
     * @var ResponseInterface|null
     */
    private ?ResponseInterface $response = null;

    /**
     * @param string $message
     * @param int $code
     * @param Exception|null $innerException
     */
    public function __construct(string $message = "", int $code = 0, ?Exception $innerException = null) {
        parent::__construct($message, $code, $innerException);
    }

    /**
     * Set raw response from API
     *
     * @param ResponseInterface $response
     * @return void
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * Return raw response from API
     *
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
