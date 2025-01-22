<?php

namespace Microsoft\Kiota\Abstractions\Serialization;

class ParseNodeHelper
{
    /**
     * Merge a collection of Parsable field deserializers.
     * @param Parsable|null ...$targets
     * @return array<string,callable(ParseNode):void>
     */
    public static function mergeDeserializersForIntersectionWrapper(?Parsable ...$targets): array
    {
        if (empty($targets)) {
            return [];
        }
        $result = [];
        for ($i = 0; $i < count($targets); $i++) {
            if ($targets[$i] !== null) {
                $targetFieldDeserializers = $targets[$i]->getFieldDeserializers();

                foreach ($targetFieldDeserializers as $key => $callable) {
                    if (!array_key_exists($key, $result)) {
                        $result[$key] = $callable;
                    }
                }
            }
        }
        return $result;
    }
}
