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
     * @throws Exception when unable to parse the DateInterval string
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

        // Strip fractional seconds, e.g., PT33.48S => PT33S
        $str = preg_replace('/(\d+)\.\d+S$/', '$1S', $str);

        if (is_null($str)) {
            throw new Exception("Invalid DateInterval string: '$value'");
        }

        $dateInterval = new DateInterval($str);
        $dateInterval->invert = $invert;

        return $dateInterval;
    }
}
