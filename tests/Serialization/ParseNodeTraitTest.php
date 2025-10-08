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

    public function testStripsFractionalSecondsSecondsOnly(): void
    {
        $parseNode = new class
        {
            use ParseNodeFromStringTrait {
                parseDateIntervalFromString as public;
            }
        };
        $di = $parseNode->parseDateIntervalFromString('PT33.48S');

        $this->assertInstanceOf(\DateInterval::class, $di);
        $this->assertEquals(33, $di->s, 'Fractional seconds should be stripped to 33 seconds');
        $this->assertEquals(0, $di->i, 'No minutes expected');
    }

    public function testStripsFractionalSecondsWithMinutes(): void
    {
        $parseNode = new class
        {
            use ParseNodeFromStringTrait {
                parseDateIntervalFromString as public;
            }
        };
        $di = $parseNode->parseDateIntervalFromString('PT1M33.48S');

        $this->assertInstanceOf(\DateInterval::class, $di);
        $this->assertEquals(1, $di->i, 'Minutes should be preserved (1 minute)');
        $this->assertEquals(33, $di->s, 'Seconds fractional part should be stripped to 33 seconds');
    }


    public function testNegativeIntervalKeepsInvertFlag(): void
    {
        $parseNode = new class
        {
            use ParseNodeFromStringTrait {
                parseDateIntervalFromString as public;
            }
        };
        $di = $parseNode->parseDateIntervalFromString('-PT33.48S');

        $this->assertInstanceOf(\DateInterval::class, $di);
        $this->assertEquals(33, $di->s);
        $this->assertEquals(1, $di->invert, 'Negative intervals should set invert = 1');
    }

    public function testDoesNotModifyValidWholeSecondString(): void
    {
        $parseNode = new class
        {
            use ParseNodeFromStringTrait {
                parseDateIntervalFromString as public;
            }
        };
        $di = $parseNode->parseDateIntervalFromString('PT12S');

        $this->assertEquals(12, $di->s);
        $this->assertEquals(0, $di->invert);
    }
}
