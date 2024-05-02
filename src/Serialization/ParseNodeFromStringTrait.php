<?php

namespace Microsoft\Kiota\Abstractions\Serialization;


use DateInterval;
use Exception;

/**
 * This trait contains utility functions for parsing strings to different types.
 */
trait ParseNodeFromStringTrait
{
    /**
     * @throws Exception
     */
    private function parseDateIntervalFromString(string $value): DateInterval
    {
        $negativeValPosition = strpos($value, '-');
        $invert = 0;
        $str = $value;
        if ($negativeValPosition !== false && $negativeValPosition === 0) {
            // Invert the interval
            $invert = 1;
            $str = substr($value, 1);
        }
        $dateInterval = new DateInterval($str);
        $dateInterval->invert = $invert;
        return $dateInterval;
    }
}
