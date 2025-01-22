<?php
/**
 * Copyright (c) Microsoft Corporation.  All Rights Reserved.
 * Licensed under the MIT License.  See License in the project root
 * for license information.
 */


namespace Microsoft\Kiota\Abstractions\Types;

use UnexpectedValueException;

/**
 * Class TypeUtils
 * @package Microsoft\Kiota\Abstractions\Types
 * @copyright 2023 Microsoft Corporation
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://learn.microsoft.com/en-us/openapi/kiota/
 */
class TypeUtils
{
    /**
     * Validates contents of a collection are of the expected $type
     * @param array<mixed>|null $values
     * @param string $type primitive type or class string
     */
    public static function validateCollectionValues($values, string $type): void
    {
        if (is_array($values)) {
            foreach ($values as $value) {
                $debugType = get_debug_type($value);
                if ($type !== $debugType && !is_subclass_of($value, $type)) {
                    throw new UnexpectedValueException("Collection of type=$type contains value of type=$debugType");
                }
            }
        }

    }
}
