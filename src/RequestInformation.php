<?php
namespace Microsoft\Kiota\Abstractions;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use InvalidArgumentException;
use League\Uri\Contracts\UriException;
use League\Uri\UriTemplate;
use Microsoft\Kiota\Abstractions\Serialization\Parsable;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class RequestInformation {
    /** @var string $RAW_URL_KEY */
    public static string $RAW_URL_KEY = 'request-raw-url';
    /** @var string $urlTemplate The url template for the current request */
    public string $urlTemplate;
    /**
     * The path parameters for the current request
     * @var array<string,mixed> $pathParameters
     */
    public array $pathParameters = [];

    /** @var string $uri */
    private string $uri;
    /**
     * @var string The HTTP method for the request
     */
    public string $httpMethod;
    /** @var array<string,mixed> The Query Parameters of the request. */
    public array $queryParameters = [];
    /** @var RequestHeaders  The Request Headers. */
    private RequestHeaders $headers;
    /** @var StreamInterface|null $content The Request Body. */
    public ?StreamInterface $content = null;
    /** @var array<string,RequestOption> */
    private array $requestOptions = [];
    /** @var string $binaryContentType */
    private static string $binaryContentType = 'application/octet-stream';
    /** @var non-empty-string $contentTypeHeader */
    public static string $contentTypeHeader = 'Content-Type';
    private static AnnotationReader $annotationReader;
    /**
     * @var ObservabilityOptions $observabilityOptions
     */
    private ObservabilityOptions $observabilityOptions;
    /** @var TracerInterface $tracer */
    private TracerInterface $tracer;
    /**
     * @param ObservabilityOptions|null $observabilityOptions
     */
    public function __construct(?ObservabilityOptions $observabilityOptions = null)
    {
        $this->headers = new RequestHeaders();
        $this->observabilityOptions = $observabilityOptions ?? new ObservabilityOptions();
        $this->tracer = $this->observabilityOptions::getTracer();
        // Init annotation utils
        self::$annotationReader = new AnnotationReader();
    }

    /** Gets the URI of the request.
     * @return string
     * @throws UriException
     */
    public function getUri(): string {
        if (!empty($this->uri)) {
            return $this->uri;
        }
        if(array_key_exists(self::$RAW_URL_KEY, $this->pathParameters)
            && is_string($this->pathParameters[self::$RAW_URL_KEY])) {
            $this->setUri($this->pathParameters[self::$RAW_URL_KEY]);
        } else {
            $template = new UriTemplate($this->urlTemplate);
            if (substr_count(strtolower($this->urlTemplate), '{+baseurl}') > 0 && !isset($this->pathParameters['baseurl'])) {
                throw new InvalidArgumentException('"PathParameters must contain a value for "baseurl" for the url to be built.');
            }

            foreach ($this->pathParameters as $key => $pathParameter) {
                $this->pathParameters[$key] = $this->sanitizeValue($pathParameter);
            }

            foreach ($this->queryParameters as $key => $queryParameter) {
                $this->queryParameters[$key] = $this->sanitizeValue($queryParameter);
            }
            $params = array_merge($this->pathParameters, $this->queryParameters);

            return $template->expand($params);
        }
        return $this->uri;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function sanitizeValue($value) {
        if (is_object($value) && is_a($value, DateTime::class)) {
            return $value->format(DateTimeInterface::ATOM);
        }
        return $value;
    }

    /**
     * Sets the URI of the request.
     */
    public function setUri(string $uri): void {
        if (empty($uri)) {
            throw new InvalidArgumentException('$uri cannot be empty.');
        }
        $this->uri = $uri;
        $this->queryParameters = [];
        $this->pathParameters = [];
    }

    /**
     * Gets the request options for this request. Options are unique by type. If an option of the same type is added twice, the last one wins.
     * @return array<string,RequestOption> the request options for this request.
     */
    public function getRequestOptions(): array {
        return $this->requestOptions;
    }

    /**
     * Adds request option(s) to this request.
     * @param RequestOption ...$options the request option to add.
     */
    public function addRequestOptions(RequestOption ...$options): void {
        if (empty($options)) {
            return;
        }
        foreach ($options as $option) {
            $this->requestOptions[get_class($option)] = $option;
        }
    }

    /**
     * Removes request option(s) from this request.
     * @param RequestOption ...$options the request option to remove.
     */
    public function removeRequestOptions(RequestOption ...$options): void {
        foreach ($options as $option) {
            unset($this->requestOptions[get_class($option)]);
        }
    }

    /**
     * Sets the request body to be a binary stream.
     * @param StreamInterface $value the binary stream
     */
    public function setStreamContent(StreamInterface $value): void {
        $this->content = $value;
        $this->headers->add(self::$contentTypeHeader, self::$binaryContentType);
    }

    /**
     * Sets the request body from a model using the specified content type.
     *
     * @param RequestAdapter $requestAdapter The adapter service to get the serialization writer from.
     * @param string $contentType the content type.
     * @param Parsable $value the models.
     */
    public function setContentFromParsable(RequestAdapter $requestAdapter, string $contentType, Parsable $value): void {
        $span = $this->tracer->spanBuilder('setContentFromParsableCollection')
            ->startSpan();
        $scope = $span->activate();
        try {
            $writer = $requestAdapter->getSerializationWriterFactory()->getSerializationWriter($contentType);
            $writer->writeObjectValue(null, $value);
            $span->setAttribute(ObservabilityOptions::REQUEST_TYPE_KEY, get_class($value));
            $this->headers->add(self::$contentTypeHeader, $contentType);
            $this->content = $writer->getSerializedContent();
            $span->setStatus(StatusCode::STATUS_OK);
        } catch (Exception $exception) {
            $ex = new RuntimeException('could not serialize payload.', 1, $exception);
            $span->recordException($ex);
            $span->setStatus(StatusCode::STATUS_ERROR);
            throw $ex;
        } finally {
            $scope->detach();
            $span->end();
        }
    }

    /**
     * Sets the request body from a collection of models using the specified content type
     *
     * @param RequestAdapter $requestAdapter
     * @param string $contentType
     * @param Parsable[] $values
     * @return void
     */
    public function setContentFromParsableCollection(RequestAdapter $requestAdapter, string $contentType, array $values): void
    {
        $span = $this->tracer->spanBuilder('setContentFromParsableCollection')
            ->startSpan();
        $scope = $span->activate();
        try {
            $writer = $requestAdapter->getSerializationWriterFactory()->getSerializationWriter($contentType);
            $writer->writeCollectionOfObjectValues(null, $values);
            $span->setAttribute(self::$contentTypeHeader, $contentType);
            if (!empty($values)) {
                $span->setAttribute(ObservabilityOptions::REQUEST_TYPE_KEY, get_class($values[0]));
            }
            $this->headers->add(self::$contentTypeHeader, $contentType);
            $this->content = $writer->getSerializedContent();
        } catch (Exception $exception) {
            throw new RuntimeException('could not serialize payload.', 1, $exception);
        } finally {
            $scope->detach();
            $span->end();
        }
    }

    /**
     * Sets the request body from a scalar value(https://www.php.net/manual/en/language.types.intro.php)
     *
     * @param RequestAdapter $requestAdapter
     * @param string $contentType
     * @param int|string|bool|float $value
     * @return void
     */
    public function setContentFromScalar(RequestAdapter $requestAdapter, string $contentType, $value): void {
        $span = $this->tracer->spanBuilder('setContentFromScalar')
            ->startSpan();
        $scope = $span->activate();
        try {
            $writer = $requestAdapter->getSerializationWriterFactory()->getSerializationWriter($contentType);
            $writer->writeAnyValue(null, $value);
            $span->setAttribute(self::$contentTypeHeader, $contentType);
            $span->setAttribute(ObservabilityOptions::REQUEST_TYPE_KEY, gettype($value));
            $this->headers->add(self::$contentTypeHeader, $contentType);
            $this->content = $writer->getSerializedContent();
            $span->setStatus(StatusCode::STATUS_OK);
        } catch (Exception $exception) {
            $ex =  new RuntimeException('could not serialize payload.', 1, $exception);
            $span->recordException($ex);
            $span->setStatus(StatusCode::STATUS_ERROR);
            throw $ex;
        } finally {
            $scope->detach();
            $span->end();
        }
    }

    /**
     *  Sets the request body from a collection of scalar values(https://www.php.net/manual/en/language.types.intro.php) using the $contentType
     *
     * @param RequestAdapter $requestAdapter
     * @param string $contentType
     * @param array<int|float|string|bool> $values
     * @return void
     */
    public function setContentFromScalarCollection(RequestAdapter $requestAdapter, string $contentType, array $values): void {
        $span = $this->tracer->spanBuilder('setContentFromScalarCollection')
            ->startSpan();
        $scope = $span->activate();
        try {
            $writer = $requestAdapter->getSerializationWriterFactory()->getSerializationWriter($contentType);
            $writer->writeCollectionOfPrimitiveValues(null, $values);
            $span->setAttribute(self::$contentTypeHeader, $contentType);
            if (!empty($values)) {
                $span->setAttribute(ObservabilityOptions::REQUEST_TYPE_KEY, gettype($values[0]));
            }
            $this->headers->add(self::$contentTypeHeader, $contentType);
            $this->content = $writer->getSerializedContent();
            $span->setStatus(StatusCode::STATUS_OK);
        } catch (Exception $exception) {
            $ex = new RuntimeException('could not serialize payload.', 1, $exception);
            $span->recordException($ex);
            $span->setStatus(StatusCode::STATUS_ERROR);
            throw $ex;
        } finally {
            $scope->detach();
            $span->end();
        }
    }

    /**
     * Set the query parameters.
     * @param object|null $queryParameters
     */
    public function setQueryParameters(?object $queryParameters): void {
        if (!$queryParameters) return;
        $reflectionClass = new \ReflectionClass($queryParameters);
        foreach ($reflectionClass->getProperties() as $classProperty) {
            $propertyValue = $classProperty->getValue($queryParameters);
            $propertyAnnotation = self::$annotationReader->getPropertyAnnotation($classProperty, QueryParameter::class);
            if ($propertyValue) {
                if ($propertyAnnotation) {
                    $this->queryParameters[$propertyAnnotation->name] = $propertyValue;
                    continue;
                }
                $this->queryParameters[$classProperty->name] = $propertyValue;
            }
        }
    }

    /**
     * Set the path parameters.
     * @param array<string,mixed> $pathParameters
     */
    public function setPathParameters(array $pathParameters): void {
        $this->pathParameters = $pathParameters;
    }

    /**
     * Set the headers and update if we already have some headers.
     * @param array<string, array<string>|string> $headers
     */
    public function addHeaders(array $headers): void
    {
        $this->headers->putAll($headers);
    }

    /**
     * Get the headers and update if we already have some headers.
     * @return RequestHeaders
     */
    public function getHeaders(): RequestHeaders
    {
        return $this->headers;
    }

    /**
     * @param array<string,array<string>|string> $headers
     * @return void
     */
    public function setHeaders(array $headers): void
    {
        $this->headers->clear();
        $this->addHeaders($headers);
    }

    /**
     * Removes header with key from the request headers.
     * @param string $key
     * @return void
     */
    public function removeHeader(string $key): void
    {
        $this->headers->remove($key);
    }

    /**
     * Add value to header with specific key.
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addHeader(string $key, string $value): void
    {
        $this->headers->add($key, $value);
    }
}
