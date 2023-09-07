<?php

namespace Microsoft\Kiota\Abstractions;

use OpenTelemetry\API\Common\Instrumentation\Globals;
use OpenTelemetry\API\Trace\TracerInterface;

class ObservabilityOptions implements RequestOption
{
    private static ?TracerInterface $tracer = null;
    private const OBSERVABILITY_TRACER_NAME = 'microsoft-php-kiota-abstractions';
    public const REQUEST_TYPE_KEY = "com.microsoft.kiota.request.type";

    /**
     * @return TracerInterface
     */
    public static function getTracer(): TracerInterface
    {
        if (self::$tracer === null) {
            self::$tracer = Globals::tracerProvider()->getTracer(self::OBSERVABILITY_TRACER_NAME, Constants::VERSION);
        }
        return self::$tracer;
    }

    public static function setTracer(TracerInterFace $tracer): void
    {
        self::$tracer = $tracer;
    }
}
