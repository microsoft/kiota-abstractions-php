<?php

namespace Microsoft\Kiota\Abstractions\Tests;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Microsoft\Kiota\Abstractions\Enum;
use Microsoft\Kiota\Abstractions\HttpMethod;
use Microsoft\Kiota\Abstractions\RequestInformation;
use Microsoft\Kiota\Abstractions\Types\Date;
use Microsoft\Kiota\Abstractions\Types\Time;
use PHPUnit\Framework\TestCase;
use Microsoft\Kiota\Abstractions\QueryParameter;

class RequestInformationTest extends TestCase {
    private RequestInformation $requestInformation;

    protected function setUp(): void {
        $this->requestInformation = new RequestInformation();
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
}

class TestQueryParameter {
    /**
     * @var string[]|null
     * @QueryParameter("%24select")
     */
    public ?array $select = null;
    public bool $count = false;
    public int $top = 10; // no annotation
    /** @var array<TestEnum>|null */
    public ?array $enum = null;
}

class TestEnum extends Enum {
    public const A = "a";
    public const B = "b";
}
