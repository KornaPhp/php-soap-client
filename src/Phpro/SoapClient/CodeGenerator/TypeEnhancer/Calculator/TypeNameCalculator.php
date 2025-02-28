<?php
declare(strict_types=1);

namespace Phpro\SoapClient\CodeGenerator\TypeEnhancer\Calculator;

use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Soap\Engine\Metadata\Model\XsdType;
use function Psl\Type\non_empty_string;

final class TypeNameCalculator
{
    public function __invoke(XsdType $type): string
    {
        $meta = $type->getMeta();
        $isSimpleType = $meta->isSimple()->unwrapOr(false);
        $isGlobalEnum = $isSimpleType && $meta->enums()->isSome() && !$meta->isLocal()->unwrapOr(false);

        // For non-simple types or backed enums, we always want to use the name of the type.
        if (!$isSimpleType || $isGlobalEnum) {
            return $type->getName();
        }

        $normalizedTypeName = Normalizer::normalizeDataType(non_empty_string()->assert($type->getName()));
        $isKnownType = Normalizer::isKnownType($normalizedTypeName);

        // For lists - the base-type of 'array' is being used
        // If the type is not known, It consists from nested member types.
        // If no member types are know, mixed is returned
        $isList = $meta->isList()->unwrapOr(false);
        if ($isList) {
            $memberType = $type->getMemberTypes()[0] ?? 'mixed';
            return $isKnownType ? $normalizedTypeName : $memberType;
        }

        if ($isKnownType) {
            return $normalizedTypeName;
        }

        return $type->getBaseTypeOrFallbackToName();
    }
}
