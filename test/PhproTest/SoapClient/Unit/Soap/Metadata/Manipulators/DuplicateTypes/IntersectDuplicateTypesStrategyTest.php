<?php

declare(strict_types=1);

namespace PhproTest\SoapClient\Unit\Soap\Metadata\Manipulators\DuplicateTypes;

use Phpro\SoapClient\Soap\Metadata\Manipulators\DuplicateTypes\IntersectDuplicateTypesStrategy;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypesManipulatorInterface;
use PHPUnit\Framework\TestCase;
use Soap\Engine\Metadata\Collection\PropertyCollection;
use Soap\Engine\Metadata\Collection\TypeCollection;
use Soap\Engine\Metadata\Model\Property;
use Soap\Engine\Metadata\Model\Type;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;

class IntersectDuplicateTypesStrategyTest extends TestCase
{
    public function it_is_a_types_manipulator(): void
    {
        $strategy = new IntersectDuplicateTypesStrategy();
        self::assertInstanceOf(TypesManipulatorInterface::class, $strategy);
    }

    /** @test */
    public function it_can_intersect_duplicate_types(): void
    {
        $strategy = new IntersectDuplicateTypesStrategy();
        $types = new TypeCollection(
            new Type(XsdType::create('file'), new PropertyCollection(
                new Property('prop1', XsdType::create('string')),
                new Property('prop3', XsdType::create('string')),
                new Property('prop4', XsdType::create('string')),
            )),
            new Type(XsdType::create('file'), new PropertyCollection(
                new Property('prop1', XsdType::create('int')),
                new Property('prop2', XsdType::create('string')),
            )),
            new Type(XsdType::create('uppercased'), new PropertyCollection()),
            new Type(XsdType::create('Uppercased'), new PropertyCollection()),
            new Type(XsdType::create('with-specialchar'), new PropertyCollection()),
            new Type(XsdType::create('with*specialchar'), new PropertyCollection()),
            new Type(XsdType::create('not-duplicate'), new PropertyCollection()),
            new Type(XsdType::create('CASEISDIFFERENT'), new PropertyCollection()),
            new Type(XsdType::create('Case-is-different'), new PropertyCollection())
        );

        $manipulated = $strategy($types);
        $nullable = static fn(TypeMeta $meta) => $meta->withIsNullable(true);

        self::assertInstanceOf(TypeCollection::class, $manipulated);
        self::assertEquals(
            [
                new Type(XsdType::create('file'), new PropertyCollection(
                    new Property('prop1', XsdType::create('int')),
                    new Property('prop3', XsdType::create('string')->withMeta($nullable)),
                    new Property('prop4', XsdType::create('string')->withMeta($nullable)),
                    new Property('prop2', XsdType::create('string')->withMeta($nullable)),
                )),
                new Type(XsdType::create('uppercased'), new PropertyCollection()),
                new Type(XsdType::create('with-specialchar'), new PropertyCollection()),
                new Type(XsdType::create('not-duplicate'), new PropertyCollection()),
                new Type(XsdType::create('CASEISDIFFERENT'), new PropertyCollection()),
                new Type(XsdType::create('Case-is-different'), new PropertyCollection()),
            ],
            iterator_to_array($manipulated)
        );
    }
}
