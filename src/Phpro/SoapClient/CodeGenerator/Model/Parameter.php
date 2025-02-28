<?php

namespace Phpro\SoapClient\CodeGenerator\Model;

use Phpro\SoapClient\CodeGenerator\TypeEnhancer\Calculator\TypeNameCalculator;
use Phpro\SoapClient\CodeGenerator\Util\Normalizer;
use Soap\Engine\Metadata\Model\Parameter as MetadataParameter;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;
use function Psl\Type\non_empty_string;

class Parameter
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var non-empty-string
     */
    private string $type;

    /**
     * @var non-empty-string
     */
    private string $namespace;

    private XsdType $xsdType;

    private TypeMeta $meta;

    /**
     * @internal - Use Parameter::fromMetadata instead
     *
     * Parameter constructor.
     *
     * @param non-empty-string $name
     * @param non-empty-string $type
     * @param non-empty-string $namespace
     */
    public function __construct(string $name, string $type, string $namespace, XsdType $xsdType)
    {
        $this->name = $name;
        $this->type = $type;
        $this->namespace = $namespace;
        $this->xsdType = $xsdType;
        $this->meta = $xsdType->getMeta();
    }

    /**
     * @param non-empty-string $parameterNamespace
     */
    public static function fromMetadata(string $parameterNamespace, MetadataParameter $parameter): Parameter
    {
        $type = $parameter->getType();
        $typeName = (new TypeNameCalculator())($type);

        return new self(
            Normalizer::normalizeProperty(non_empty_string()->assert($parameter->getName())),
            Normalizer::normalizeDataType(non_empty_string()->assert($typeName)),
            Normalizer::normalizeNamespace($parameterNamespace),
            $type
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function getType(): string
    {
        if (Normalizer::isKnownType($this->type)) {
            return $this->type;
        }

        return '\\'.$this->namespace.'\\'.Normalizer::normalizeClassname($this->type);
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getXsdType(): XsdType
    {
        return $this->xsdType;
    }

    public function getMeta(): TypeMeta
    {
        return $this->meta;
    }
}
