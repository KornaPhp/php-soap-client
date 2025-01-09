<?php

declare(strict_types=1);

namespace Phpro\SoapClient\Soap\Metadata\Manipulators\DuplicateTypes;

use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Phpro\SoapClient\Soap\Metadata\Manipulators\TypesManipulatorInterface;
use Soap\Engine\Metadata\Collection\PropertyCollection;
use Soap\Engine\Metadata\Collection\TypeCollection;
use Soap\Engine\Metadata\Model\Property;
use Soap\Engine\Metadata\Model\Type;
use Soap\Engine\Metadata\Model\TypeMeta;
use function Psl\Iter\contains;
use function Psl\Iter\first;
use function Psl\Iter\reduce;
use function Psl\Type\instance_of;
use function Psl\Type\non_empty_string;
use function Psl\Vec\flat_map;
use function Psl\Vec\map;
use function Psl\Vec\values;

final class IntersectDuplicateTypesStrategy implements TypesManipulatorInterface
{
    public function __invoke(TypeCollection $allTypes): TypeCollection
    {
        return new TypeCollection(...array_values($allTypes->reduce(
            function (array $result, Type $type) use ($allTypes): array {
                $name = Normalizer::normalizeClassname(non_empty_string()->assert($type->getName()));
                if (array_key_exists($name, $result)) {
                    return $result;
                }

                return array_merge(
                    $result,
                    [
                        $name => $this->intersectTypes($this->fetchAllTypesNormalizedByName($allTypes, $name))
                    ]
                );
            },
            []
        )));
    }

    private function intersectTypes(TypeCollection $duplicateTypes): Type
    {
        $type = instance_of(Type::class)->assert(first($duplicateTypes));

        return new Type(
            $type->getXsdType(),
            $this->uniqueProperties(
                ...map($duplicateTypes, static fn (Type $type) => $type->getProperties())
            )
        );
    }

    private function fetchAllTypesNormalizedByName(TypeCollection $types, string $name): TypeCollection
    {
        return $types->filter(static function (Type $type) use ($name): bool {
            return Normalizer::normalizeClassname(non_empty_string()->assert($type->getName())) === $name;
        });
    }

    private function uniqueProperties(PropertyCollection ...$types): PropertyCollection
    {
        $allProps = flat_map($types, static fn (PropertyCollection $props) => $props);
        $typePropNames = map(
            $types,
            static fn (PropertyCollection $props): array => $props->map(static fn (Property $prop) => $prop->getName())
        );
        $intersectedPropNames = array_intersect(...$typePropNames);

        return new PropertyCollection(
            ...values(
                reduce(
                    $allProps,
                    /**
                     * @param array<string, Property> $result
                     *
                     * @return array<string, Property>
                     */
                    static function (array $result, Property $prop) use ($intersectedPropNames): array {
                        $result[$prop->getName()] = new Property(
                            $prop->getName(),
                            $prop->getType()->withMeta(
                                static function (TypeMeta $meta) use ($prop, $intersectedPropNames): TypeMeta {
                                    if (contains($intersectedPropNames, $prop->getName())) {
                                        return $meta;
                                    }

                                    return $meta->withIsNullable(true);
                                }
                            )
                        );

                        return $result;
                    },
                    []
                )
            )
        );
    }
}
