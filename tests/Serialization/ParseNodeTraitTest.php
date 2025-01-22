<?php

namespace Microsoft\Kiota\Abstractions\Tests\Serialization;

use DateInterval;
use Microsoft\Kiota\Abstractions\Serialization\ParseNodeFromStringTrait;
use Microsoft\Kiota\Abstractions\Serialization\SerializationWriterToStringTrait;
use PHPUnit\Framework\TestCase;

class ParseNodeTraitTest extends TestCase
{
    public function testDateIntervalValueFromStringNegative(): void
    {
        $parseNode = new class
        {
            use ParseNodeFromStringTrait {parseDateIntervalFromString as public;}
        };
        $value = $parseNode->parseDateIntervalFromString('-P1DT23H59M19S');
        $dateInterval = new DateInterval('P1DT23H59M19S');
        $dateInterval->invert = 1;

        $this->assertEquals($dateInterval, $value);
    }
    public function testDateIntervalValueFromString(): void
    {
        $parseNode = new class
        {
            use ParseNodeFromStringTrait {parseDateIntervalFromString as public;}
        };
        $value = $parseNode->parseDateIntervalFromString('P1DT23H59M19S');
        $dateInterval = new DateInterval('P1DT23H59M19S');

        $this->assertEquals($dateInterval, $value);
    }
}
