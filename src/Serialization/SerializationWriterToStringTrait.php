<?php

namespace Microsoft\Kiota\Abstractions\Serialization;

use DateInterval;
use DateTime;
use DateTimeInterface;

/**
 * This Trait contains common to-string conversion for common types.
 */
trait SerializationWriterToStringTrait
{
    private function getDateIntervalValueAsString(DateInterval $value): string
    {
        $year = $value->y > 0 ? "%yY" : "";
        $month = $value->m > 0 ? "%mM" : "";
        $day = $value->d > 0 ? '%dD' : "";
        $hour = $value->h > 0 ? '%hH' : "";
        $minute = $value->i > 0 ? '%iM' : "";
        $second = $value->s > 0 ? '%sS' : "";
        $timePart = $hour.$minute.$second;
        $time = !empty($timePart) ? "T$timePart" : '';
        return $value->format("%rP$year$month{$day}$time");
    }
    private function getDateTimeValueAsString(DateTime $value): string
    {
        return $value->format(DateTimeInterface::RFC3339);
    }

    private function getBooleanValueAsString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    private function getStringValueAsEscapedString(string $value): string
    {
        return addcslashes($value, "\\\r\n\"\t");
    }
}
