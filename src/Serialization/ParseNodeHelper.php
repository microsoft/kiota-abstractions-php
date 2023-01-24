<?php

namespace Microsoft\Kiota\Abstractions\Serialization;

class ParseNodeHelper
{
    /**
     * Merge a collection of Parsable field deserializers.
     * @param Parsable ...$targets
     * @return array<string,callable(ParseNode):void>
     */
    public static function mergeDeserializersForIntersectionWrapper(Parsable ...$targets): array
    {
        if (count($targets) === 0) {
            return [];
        }
        $result = $targets[0]->getFieldDeserializers();
        for ($i = 1; $i < count($targets); $i++) {
            $targetFieldDeserializers = $targets[$i]->getFieldDeserializers();

            foreach ($targetFieldDeserializers as $key => $callable) {
                if (!array_key_exists($key, $result)) {
                    $result[$key] = $callable;
                }
            }
        }
        return $result;
    }
}
