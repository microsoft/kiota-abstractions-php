<?php

namespace Microsoft\Kiota\Abstractions;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\TracerInterface;

class ObservabilityOptions implements RequestOption
{
    private static ?TracerInterface $tracer = null;
    private const OBSERVABILITY_TRACER_NAME = 'microsoft.kiota.abstractions:microsoft-php-kiota-abstractions';
    public const REQUEST_TYPE_KEY = "com.microsoft.kiota.request.type";

    public function __construct()
    {
        self::$tracer = Globals::tracerProvider()->getTracer(self::OBSERVABILITY_TRACER_NAME, Constants::VERSION);
    }

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
