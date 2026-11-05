<?php

namespace Microsoft\Kiota\Abstractions\Tests;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Microsoft\Kiota\Abstractions\Enum;
use Microsoft\Kiota\Abstractions\HttpMethod;
use Microsoft\Kiota\Abstractions\MultiPartBody;
use Microsoft\Kiota\Abstractions\ObservabilityOptions;
use Microsoft\Kiota\Abstractions\RequestAdapter;
use Microsoft\Kiota\Abstractions\RequestInformation;
use Microsoft\Kiota\Abstractions\RequestOption;
use Microsoft\Kiota\Abstractions\Serialization\Parsable;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriter;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriterFactory;
use Microsoft\Kiota\Abstractions\Types\Date;
use Microsoft\Kiota\Abstractions\Types\Time;
use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Context\ScopeInterface;
use PHPUnit\Framework\TestCase;
use Microsoft\Kiota\Abstractions\QueryParameter;

class RequestInformationTest extends TestCase {
    private RequestInformation $requestInformation;
    private ?TracerInterface $originalTracer = null;

    protected function setUp(): void {
        $this->requestInformation = new RequestInformation();
        $this->originalTracer = ObservabilityOptions::getTracer();
    }

    protected function tearDown(): void {
        if ($this->originalTracer !== null) {
            ObservabilityOptions::setTracer($this->originalTracer);
        }
        parent::tearDown();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetUri(): void{
        $pathParameters = [
            'baseurl' => 'https://google.com',
            'user%2Did' => 'silas',
        ];
        $queryParameters = ['%24select' => ['subject', 'importance']];
        $this->requestInformation->urlTemplate = '{+baseurl}/{user%2Did}/mails{?%24select}';
        $this->requestInformation->setPathParameters($pathParameters);
        $this->requestInformation->queryParameters = $queryParameters;
        $this->assertEquals("https://google.com/silas/mails?%24select=subject,importance", $this->requestInformation->getUri());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetQueryParameters(): void {
        $this->requestInformation->urlTemplate = '{?%24select,top,%24count,enum}';

        $queryParam = new TestQueryParameter();
        $queryParam->enum = [new TestEnum('a'), new TestEnum('b')];
        $this->requestInformation->setQueryParameters($queryParam);
        $this->assertEquals('?top=10&enum=a,b', $this->requestInformation->getUri());
        $this->assertEquals(2, sizeof($this->requestInformation->queryParameters));
        $queryParam->select = ['displayName', 'age'];
        $this->requestInformation->setQueryParameters($queryParam);
        $this->assertEquals(3, sizeof($this->requestInformation->queryParameters));
        $this->assertArrayHasKey('%24select', $this->requestInformation->queryParameters);
        $this->assertEquals(['displayName', 'age'], $this->requestInformation->queryParameters['%24select']);
        $this->assertArrayHasKey('top', $this->requestInformation->queryParameters);
        $this->assertEquals('?%24select=displayName,age&top=10&enum=a,b', $this->requestInformation->getUri());
    }

    /**
     * Regression test for the native docblock parser that replaced
     * `doctrine/annotations`. Validates that multiple `@QueryParameter("name")`
     * annotations on the same class — including percent-encoded names and
     * different whitespace around the argument — are resolved to the declared
     * wire name, while unannotated properties fall back to the PHP field name.
     *
     * @throws InvalidArgumentException
     */
    public function testSetQueryParametersResolvesDocblockAnnotation(): void {
        $this->requestInformation->urlTemplate = '{?%24select,%24filter,%24orderby,plain}';

        $queryParam = new TestQueryParameterDocblock();
        $queryParam->select  = ['displayName', 'age'];
        $queryParam->filter  = "startswith(displayName,'A')";
        $queryParam->orderby = 'displayName desc';
        $queryParam->plain   = 'keep-me';
        $this->requestInformation->setQueryParameters($queryParam);

        $this->assertEquals(4, sizeof($this->requestInformation->queryParameters));
        $this->assertArrayHasKey('%24select', $this->requestInformation->queryParameters);
        $this->assertEquals(['displayName', 'age'], $this->requestInformation->queryParameters['%24select']);
        $this->assertArrayHasKey('%24filter', $this->requestInformation->queryParameters);
        $this->assertEquals("startswith(displayName,'A')", $this->requestInformation->queryParameters['%24filter']);
        $this->assertArrayHasKey('%24orderby', $this->requestInformation->queryParameters);
        $this->assertEquals('displayName desc', $this->requestInformation->queryParameters['%24orderby']);
        $this->assertArrayHasKey('plain', $this->requestInformation->queryParameters);
        $this->assertEquals('keep-me', $this->requestInformation->queryParameters['plain']);
    }

    /**
     * Regression test for preserving explicit falsy query parameter values.
     * The previous `if ($propertyValue)` guard dropped `0`, `false`, and `""`
     * even when explicitly set on the query parameters object. Switched to
     * `!== null` to align with the not-null semantics used by kiota-dotnet,
     * kiota-java, and kiota-typescript.
     *
     * @throws InvalidArgumentException
     */
    public function testSetQueryParametersPreservesFalsyValues(): void {
        $this->requestInformation->urlTemplate = '{?%24count,top,search}';

        $queryParam = new TestFalsyQueryParameter();
        $queryParam->count  = false;
        $queryParam->top    = 0;
        $queryParam->search = '';
        $this->requestInformation->setQueryParameters($queryParam);

        $this->assertArrayHasKey('%24count', $this->requestInformation->queryParameters);
        $this->assertFalse($this->requestInformation->queryParameters['%24count']);
        $this->assertArrayHasKey('top', $this->requestInformation->queryParameters);
        $this->assertSame(0, $this->requestInformation->queryParameters['top']);
        $this->assertArrayHasKey('search', $this->requestInformation->queryParameters);
        $this->assertSame('', $this->requestInformation->queryParameters['search']);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testWillThrowExceptionWhenNoBaseUrl(): void {
        $this->expectException(InvalidArgumentException::class);
        $pathParameters = [
            'bad' => 'https://google.com',
            'user%2Did' => 'silas',
        ];
        $this->requestInformation->urlTemplate = '{+baseurl}/{user%2Did}/mails{?%24select}';
        $this->requestInformation->setPathParameters($pathParameters);
        $uri = $this->requestInformation->getUri();
        $this->assertEquals('', $uri);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testPathParametersOfDateTimeOffsetType(): void
    {
        // Arrange as the request builders would
        $requestInfo = new RequestInformation();
        $requestInfo->httpMethod = HttpMethod::GET;
        $requestInfo->urlTemplate = "https://localhost/getDirectRoutingCalls(fromDateTime='{fromDateTime}',toDateTime='{toDateTime}')";

        // Act
        $fromDateTime  =new DateTime("2022-08-01T2:33", new DateTimeZone('+02:00'));
        $toDateTime  = new DateTime('2022-08-02T10:00', new DateTimeZone('-1:00'));
        $requestInfo->pathParameters["fromDateTime"] = $fromDateTime;
        $requestInfo->pathParameters["toDateTime"] =  $toDateTime;

        // Assert
        $uri = $requestInfo->getUri();
        $this->assertEquals("https://localhost/getDirectRoutingCalls(fromDateTime='2022-08-01T02%3A33%3A00%2B02%3A00',toDateTime='2022-08-02T10%3A00%3A00-01%3A00')", $uri);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testPathParametersOfDateTimeType(): void
    {
        // Arrange as the request builders would
        $requestInfo = new RequestInformation();
        $requestInfo->httpMethod = HttpMethod::GET;
        $requestInfo->urlTemplate = "https://localhost/getDirectRoutingCalls(fromDateTime='{fromDateTime}',toDateTime='{toDateTime}')";

        // Act
        $fromDateTime = new DateTime("2022-08-01T2:33");
        $toDateTime  = new DateTime('2022-08-02T10:00');
        $requestInfo->pathParameters["fromDateTime"] = $fromDateTime;
        $requestInfo->pathParameters["toDateTime"] =  $toDateTime;

        // Assert
        $uri = $requestInfo->getUri();
        $this->assertEquals("https://localhost/getDirectRoutingCalls(fromDateTime='2022-08-01T02%3A33%3A00%2B00%3A00',toDateTime='2022-08-02T10%3A00%3A00%2B00%3A00')", $uri);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testPathParametersOfTimeType(): void
    {
        // Arrange as the request builders would
        $requestInfo = new RequestInformation();
        $requestInfo->httpMethod = HttpMethod::GET;
        $requestInfo->urlTemplate = "https://localhost/getDirectRoutingCalls(fromDateTime='{fromDateTime}',toDateTime='{toDateTime}')";

        // Act
        $fromDateTime = Time::createFromDateTime(new DateTime("2022-08-01T2:33", new DateTimeZone('+02:00')));
        $toDateTime  = Time::createFromDateTime(new DateTime('2022-08-02T10:00', new DateTimeZone('-1:00')));
        $requestInfo->pathParameters["fromDateTime"] = $fromDateTime;
        $requestInfo->pathParameters["toDateTime"] =  $toDateTime;

        // Assert
        $uri = $requestInfo->getUri();
        $this->assertEquals("https://localhost/getDirectRoutingCalls(fromDateTime='02%3A33%3A00',toDateTime='10%3A00%3A00')", $uri);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testPathParametersOfDateType(): void
    {
        // Arrange as the request builders would
        $requestInfo = new RequestInformation();
        $requestInfo->httpMethod = HttpMethod::GET;
        $requestInfo->urlTemplate = "https://localhost/getDirectRoutingCalls(fromDateTime='{fromDateTime}',toDateTime='{toDateTime}')";

        // Act
        $fromDateTime = Date::createFromDateTime(new DateTime("2022-08-01T2:33", new DateTimeZone('+02:00')));
        $toDateTime  = Date::createFromDateTime(new DateTime('2022-08-02T10:00', new DateTimeZone('-1:00')));
        $requestInfo->pathParameters["fromDateTime"] = $fromDateTime;
        $requestInfo->pathParameters["toDateTime"] =  $toDateTime;

        // Assert
        $uri = $requestInfo->getUri();
        $this->assertEquals("https://localhost/getDirectRoutingCalls(fromDateTime='2022-08-01',toDateTime='2022-08-02')", $uri);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testPathParametersOfDateIntervalType(): void
    {
        // Arrange as the request builders would
        $requestInfo = new RequestInformation();
        $requestInfo->httpMethod = HttpMethod::GET;
        $requestInfo->urlTemplate = "https://localhost/getDirectRoutingCalls(period='{period}')";

        // Act
        $period = DateInterval::createFromDateString('1 day 3 hours');
        $requestInfo->pathParameters["period"] =  $period;

        // Assert
        $uri = $requestInfo->getUri();
        $this->assertEquals("https://localhost/getDirectRoutingCalls(period='P1DT3H')", $uri);
    }

    public function testCanHandleBooleanTypes(): void {
        // Arrange as the request builders would
        $requestInfo = new RequestInformation();
        $requestInfo->httpMethod = HttpMethod::GET;
        $requestInfo->urlTemplate = "http://localhost/users{?%24exists}";

        $requestInfo->setPathParameters(['%24exists' => true]);
        // Assert
        $uri = $requestInfo->getUri();
        $this->assertEquals('http://localhost/users?%24exists=true', $uri);
    }

    public function testCanHandleNumericTypes(): void {
        // Arrange as the request builders would
        $requestInfo = new RequestInformation();
        $requestInfo->httpMethod = HttpMethod::GET;
        $requestInfo->urlTemplate = "{+baseurl}/users{?%24count}";

        $requestInfo->setPathParameters(['%24count' => true, 'baseurl' => 'http://localhost']);
        // Assert
        $uri = $requestInfo->getUri();
        $this->assertEquals('http://localhost/users?%24count=true', $uri);
    }

    public function testExposeTryAddRequestHeader(): void {
        // Arrange as the request builders would
        $requestInfo = new RequestInformation();

        // Assert
        $this->assertTrue($requestInfo->tryAddHeader("key", "value1"));
        $this->assertFalse($requestInfo->tryAddHeader("key", "value2"));
        $res = $requestInfo->getHeaders()->get("key");
        $this->assertEquals(1, count($res));
        $this->assertEquals("value1", $res[0]);
    }

    public function testRemoveHeaderValue(): void {
        $requestInfo = new RequestInformation();
        $requestInfo->addHeader('Accept', 'application/json');
        $requestInfo->addHeader('Accept', 'text/plain');

        $this->assertTrue($requestInfo->removeHeaderValue('Accept', 'application/json'));
        $this->assertEquals(['text/plain'], $requestInfo->getHeaders()->get('Accept'));
        $this->assertTrue($requestInfo->getHeaders()->contains('Accept'));

        $this->assertTrue($requestInfo->removeHeaderValue('Accept', 'text/plain'));
        $this->assertFalse($requestInfo->getHeaders()->contains('Accept'));
        $this->assertEmpty($requestInfo->getHeaders()->get('Accept'));

        $this->assertFalse($requestInfo->removeHeaderValue('Accept', 'not-present'));
    }

    public function testSetContentFromParsableSetsMultipartBoundaryOnContentTypeHeader(): void {
        $requestAdapter = $this->createMock(RequestAdapter::class);
        $serializationWriter = $this->createMock(SerializationWriter::class);
        $writerFactory = $this->createMock(SerializationWriterFactory::class);

        $stream = \GuzzleHttp\Psr7\Utils::streamFor('--boundary--');
        $serializationWriter->method('getSerializedContent')->willReturn($stream);
        $writerFactory->method('getSerializationWriter')->willReturn($serializationWriter);
        $requestAdapter->method('getSerializationWriterFactory')->willReturn($writerFactory);

        $requestInfo = new RequestInformation();
        $multipart = new MultiPartBody();
        $requestInfo->setContentFromParsable($requestAdapter, 'multipart/form-data', $multipart);

        $this->assertSame($stream, $requestInfo->content);
        $contentTypeHeaders = $requestInfo->getHeaders()->get('Content-Type');
        $this->assertCount(1, $contentTypeHeaders);
        $this->assertEquals("multipart/form-data; boundary={$multipart->getBoundary()}", $contentTypeHeaders[0]);
        $this->assertTrue(ctype_print($multipart->getBoundary()));
        $this->assertTrue(ctype_xdigit($multipart->getBoundary()));
    }

    public function testSetContentFromParsableWithBoundaryZero(): void {
        $requestAdapter = $this->createMock(RequestAdapter::class);
        $serializationWriter = $this->createMock(SerializationWriter::class);
        $writerFactory = $this->createMock(SerializationWriterFactory::class);

        $stream = \GuzzleHttp\Psr7\Utils::streamFor('--0--');
        $serializationWriter->method('getSerializedContent')->willReturn($stream);
        $writerFactory->method('getSerializationWriter')->willReturn($serializationWriter);
        $requestAdapter->method('getSerializationWriterFactory')->willReturn($writerFactory);

        $requestInfo = new RequestInformation();
        $multipart = new MultiPartBody('0');
        $requestInfo->setContentFromParsable($requestAdapter, 'multipart/form-data', $multipart);

        $contentTypeHeaders = $requestInfo->getHeaders()->get('Content-Type');
        $this->assertEquals(['multipart/form-data; boundary=0'], $contentTypeHeaders);
    }

    public function testSetContentFromParsableQuotesNonTokenBoundary(): void {
        $requestAdapter = $this->createMock(RequestAdapter::class);
        $serializationWriter = $this->createMock(SerializationWriter::class);
        $writerFactory = $this->createMock(SerializationWriterFactory::class);

        $stream = \GuzzleHttp\Psr7\Utils::streamFor('--custom:boundary=1--');
        $serializationWriter->method('getSerializedContent')->willReturn($stream);
        $writerFactory->method('getSerializationWriter')->willReturn($serializationWriter);
        $requestAdapter->method('getSerializationWriterFactory')->willReturn($writerFactory);

        $requestInfo = new RequestInformation();
        $multipart = new MultiPartBody('custom:boundary=1');
        $requestInfo->setContentFromParsable($requestAdapter, 'multipart/form-data', $multipart);

        $contentTypeHeaders = $requestInfo->getHeaders()->get('Content-Type');
        $this->assertEquals(['multipart/form-data; boundary="custom:boundary=1"'], $contentTypeHeaders);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testExpandsMapQueryParameterAsIndividualKeyValuePairs(): void {
        $requestInfo = new RequestInformation();
        $requestInfo->urlTemplate = 'http://localhost/articles{?query*}';
        $requestInfo->queryParameters['query'] = [
            'filter' => 'equals(published,true)',
            'sort'   => '-createdAt',
        ];

        $uri = $requestInfo->getUri();

        $this->assertStringContainsString('?', $uri);
        $this->assertStringContainsString('filter=equals%28published%2Ctrue%29', $uri);
        $this->assertStringContainsString('sort=-createdAt', $uri);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testMapQueryParameterNullValuesAreOmitted(): void {
        $requestInfo = new RequestInformation();
        $requestInfo->urlTemplate = 'http://localhost/articles{?query*}';
        $requestInfo->queryParameters['query'] = [
            'include' => 'author',
            'exclude' => null,
        ];

        $uri = $requestInfo->getUri();

        $this->assertStringContainsString('include=author', $uri);
        $this->assertStringNotContainsString('exclude', $uri);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testMapQueryParameterEmptyStringValueIsIncluded(): void {
        $requestInfo = new RequestInformation();
        $requestInfo->urlTemplate = 'http://localhost/articles{?query*}';
        $requestInfo->queryParameters['query'] = ['search' => ''];

        $uri = $requestInfo->getUri();

        $this->assertStringContainsString('search=', $uri);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testEmptyMapQueryParameterProducesNoQueryString(): void {
        $requestInfo = new RequestInformation();
        $requestInfo->urlTemplate = 'http://localhost/articles{?query*}';
        $requestInfo->queryParameters['query'] = [];

        $uri = $requestInfo->getUri();

        $this->assertStringNotContainsString('?', $uri);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testAllNullMapQueryParameterProducesNoQueryString(): void {
        $requestInfo = new RequestInformation();
        $requestInfo->urlTemplate = 'http://localhost/articles{?query*}';
        $requestInfo->queryParameters['query'] = ['a' => null, 'b' => null];

        $uri = $requestInfo->getUri();

        $this->assertStringNotContainsString('?', $uri);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testMixOfMapAndScalarQueryParameters(): void {
        $requestInfo = new RequestInformation();
        $requestInfo->urlTemplate = 'http://localhost/articles{?query*,top}';
        $requestInfo->queryParameters['query'] = ['filter' => 'active'];
        $requestInfo->queryParameters['top']   = 5;

        $uri = $requestInfo->getUri();

        $this->assertStringContainsString('filter=active', $uri);
        $this->assertStringContainsString('top=5', $uri);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testMapQueryParameterViaSetQueryParameters(): void {
        $requestInfo = new RequestInformation();
        $requestInfo->urlTemplate = 'http://localhost/articles{?query*}';

        $queryParam = new TestMapQueryParameter();
        $queryParam->query = ['filter' => 'equals(published,true)', 'sort' => '-createdAt'];
        $requestInfo->setQueryParameters($queryParam);

        $uri = $requestInfo->getUri();

        $this->assertStringContainsString('filter=equals%28published%2Ctrue%29', $uri);
        $this->assertStringContainsString('sort=-createdAt', $uri);
    }

    public function testGetRequestOptionDirectAndPolymorphic(): void {
        $requestInfo = new RequestInformation();
        $option = new TestCustomRequestOption();
        $requestInfo->addRequestOptions($option);

        $this->assertSame($option, $requestInfo->getRequestOption(TestCustomRequestOption::class));
        $this->assertSame($option, $requestInfo->getRequestOption(RequestOption::class));
        $this->assertNull($requestInfo->getRequestOption(TestOtherOption::class));
    }

    public function testGetUriThrowsOnEmptyBaseUrl(): void {
        $requestInfo = new RequestInformation();
        $requestInfo->urlTemplate = '{+baseurl}/users';
        $requestInfo->pathParameters['baseurl'] = '   ';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('PathParameters must contain a value for "baseurl" for the url to be built.');
        $requestInfo->getUri();
    }

    public function testSetContentFromParsableCollection(): void {
        $span = $this->createMock(SpanInterface::class);
        $scope = $this->createMock(ScopeInterface::class);
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $tracer = $this->createMock(TracerInterface::class);

        $tracer->expects($this->once())
            ->method('spanBuilder')
            ->with('setContentFromParsableCollection')
            ->willReturn($spanBuilder);
        $spanBuilder->expects($this->once())
            ->method('startSpan')
            ->willReturn($span);
        $span->expects($this->once())
            ->method('activate')
            ->willReturn($scope);
        $span->expects($this->once())
            ->method('setStatus')
            ->with(StatusCode::STATUS_OK);
        $span->expects($this->once())
            ->method('end');
        $scope->expects($this->once())
            ->method('detach');

        ObservabilityOptions::setTracer($tracer);

        $requestAdapter = $this->createMock(RequestAdapter::class);
        $serializationWriter = $this->createMock(SerializationWriter::class);
        $writerFactory = $this->createMock(SerializationWriterFactory::class);

        $stream = \GuzzleHttp\Psr7\Utils::streamFor('[{"id": 1}]');
        $serializationWriter->method('getSerializedContent')->willReturn($stream);
        $writerFactory->method('getSerializationWriter')->willReturn($serializationWriter);
        $requestAdapter->method('getSerializationWriterFactory')->willReturn($writerFactory);

        $requestInfo = new RequestInformation();
        $parsable = new TestParsableModel();
        $requestInfo->setContentFromParsableCollection($requestAdapter, 'application/json', [$parsable]);

        $this->assertSame($stream, $requestInfo->content);
        $this->assertEquals(['application/json'], $requestInfo->getHeaders()->get('Content-Type'));
    }

    public function testSetContentFromParsableCollectionThrowsOnWriterError(): void {
        $span = $this->createMock(SpanInterface::class);
        $scope = $this->createMock(ScopeInterface::class);
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $tracer = $this->createMock(TracerInterface::class);

        $tracer->expects($this->once())
            ->method('spanBuilder')
            ->with('setContentFromParsableCollection')
            ->willReturn($spanBuilder);
        $spanBuilder->expects($this->once())
            ->method('startSpan')
            ->willReturn($span);
        $span->expects($this->once())
            ->method('activate')
            ->willReturn($scope);
        $span->expects($this->once())
            ->method('recordException')
            ->with($this->isInstanceOf(\RuntimeException::class));
        $span->expects($this->once())
            ->method('setStatus')
            ->with(StatusCode::STATUS_ERROR);
        $span->expects($this->once())
            ->method('end');
        $scope->expects($this->once())
            ->method('detach');

        ObservabilityOptions::setTracer($tracer);

        $requestAdapter = $this->createMock(RequestAdapter::class);
        $writerFactory = $this->createMock(SerializationWriterFactory::class);
        $writerFactory->method('getSerializationWriter')->willThrowException(new Exception('writer failed'));
        $requestAdapter->method('getSerializationWriterFactory')->willReturn($writerFactory);

        $requestInfo = new RequestInformation();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('could not serialize payload.');
        $requestInfo->setContentFromParsableCollection($requestAdapter, 'application/json', []);
    }
}

class TestCustomRequestOption implements RequestOption {}
class TestOtherOption implements RequestOption {}
class TestParsableModel implements Parsable {
    public function getFieldDeserializers(): array { return []; }
    public function serialize(SerializationWriter $writer): void {}
}

class TestQueryParameter {
    /**
     * @var string[]|null
     * @QueryParameter("%24select")
     */
    public ?array $select = null;
    public ?bool $count = null;
    public int $top = 10; // no annotation
    /** @var array<TestEnum>|null */
    public ?array $enum = null;
}

class TestQueryParameterDocblock {
    /**
     * @var string[]|null
     * @QueryParameter("%24select")
     */
    public ?array $select = null;

    /**
     * @var string|null
     * @QueryParameter("%24filter")
     */
    public ?string $filter = null;

    /**
     * @var string|null
     * @QueryParameter(  "%24orderby"  )
     */
    public ?string $orderby = null;

    public ?string $plain = null; // no annotation → falls back to field name
}

class TestFalsyQueryParameter {
    /**
     * @QueryParameter("%24count")
     */
    public ?bool $count = null;
    public ?int $top = null;       // no annotation → falls back to field name
    public ?string $search = null; // no annotation → falls back to field name
}

class TestEnum extends Enum {
    public const A = "a";
    public const B = "b";
}

class TestMapQueryParameter {
    /** @var array<string,string|null>|null */
    public ?array $query = null;
}
