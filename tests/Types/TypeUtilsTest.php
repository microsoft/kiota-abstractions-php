<?php

namespace Microsoft\Kiota\Abstractions\Tests\Types;

use Microsoft\Kiota\Abstractions\Types\TypeUtils;
use PHPUnit\Framework\TestCase;

class TypeUtilsTest extends TestCase
{
    public function testSuccessfulValidation(): void
    {
        $this->expectNotToPerformAssertions();
        TypeUtils::validateCollectionValues([1, 2, 3], 'int');
        TypeUtils::validateCollectionValues(['hello', 'world'], 'string');
        TypeUtils::validateCollectionValues([1.2, 3.2], 'float');
        TypeUtils::validateCollectionValues([true, false], 'bool');
        TypeUtils::validateCollectionValues([new TestCustomType(), new TestCustomType()], TestCustomType::class);
    }

    public function testNullThrowsNoException(): void
    {
        $this->expectNotToPerformAssertions();
        TypeUtils::validateCollectionValues(null, 'string');
    }

    public function testInvalidValueThrowsException(): void
    {
        $values = [
            'string' => ['hello', 1],
            'int' => [1, 1.2],
            'bool' => [true, 1],
            'float' => [1.2, 1],
            TestCustomType::class => [new TestCustomType(), 1]
        ];

        foreach ($values as $key => $value) {
            try {
                TypeUtils::validateCollectionValues($value, $key);
            } catch (\Exception $ex) {
                $this->assertInstanceOf(\UnexpectedValueException::class, $ex);
            }
        }
    }

    public function testValidationWithChildClasses(): void
    {
        $this->expectNotToPerformAssertions();
        TypeUtils::validateCollectionValues([new ChildCustomType(), new ChildCustomType()], TestCustomType::class);
        TypeUtils::validateCollectionValues([new ChildCustomType(), new ChildCustomType()], TestInterface::class);
    }
}

class TestCustomType {

}

class ChildCustomType extends TestCustomType implements TestInterface {

}

interface TestInterface {

}
