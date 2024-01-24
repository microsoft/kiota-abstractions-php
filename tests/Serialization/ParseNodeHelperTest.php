<?php

namespace Microsoft\Kiota\Abstractions\Tests\Serialization;

use Microsoft\Kiota\Abstractions\Serialization\Parsable;
use Microsoft\Kiota\Abstractions\Serialization\ParseNode;
use Microsoft\Kiota\Abstractions\Serialization\ParseNodeHelper;
use PHPUnit\Framework\TestCase;

class ParseNodeHelperTest extends TestCase
{
    public function testMergeDeserializersForIntersectionWrapper(): void
    {
        $parsable1 = $this->createStub(Parsable::class);
        $parsable1->method('getFieldDeserializers')
            ->willReturn([
               'x' => fn (ParseNode $n) => $n->setOnAfterAssignFieldValues(fn ($x) => strval($x))
            ]);
        $parsable2 = $this->createStub(Parsable::class);
        $parsable2->method('getFieldDeserializers')
            ->willReturn([
                'y' => fn (ParseNode $n) => $n->setOnAfterAssignFieldValues(fn ($x) => strval($x))
            ]);
        $parsable3 = $this->createStub(Parsable::class);
        $parsable3->method('getFieldDeserializers')
            ->willReturn([
                'x' => fn (ParseNode $n) => $n->setOnAfterAssignFieldValues(fn ($x) => intval($x))
            ]);
        $deserializers = ParseNodeHelper::mergeDeserializersForIntersectionWrapper($parsable1, null, $parsable2, null, $parsable3);
        $this->assertEquals(
            [
                'x' => fn (ParseNode $n) => $n->setOnAfterAssignFieldValues(fn ($x) => strval($x)),
                'y' => fn (ParseNode $n) => $n->setOnAfterAssignFieldValues(fn ($x) => strval($x))
            ],
            $deserializers
        );
    }

}
