<?php

namespace spec\Phpro\SoapClient\CodeGenerator\Model;

use Phpro\SoapClient\CodeGenerator\Model\Property;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Soap\Engine\Metadata\Model\TypeMeta;
use Soap\Engine\Metadata\Model\XsdType;

/**
 * Class PropertySpec
 *
 * @package spec\Phpro\SoapClient\CodeGenerator\Model
 * @mixin Property
 */
class PropertySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('name', 'Type', 'My\Namespace', XsdType::create('Type'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Property::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('name');
    }

    function it_has_a_type()
    {
        $this->getType()->shouldReturn('\\My\\Namespace\\Type');
    }

    function it_has_a_getter_name()
    {
        $this->getterName()->shouldReturn('getName');
    }

    function it_has_a_setter_name()
    {
        $this->setterName()->shouldReturn('setName');
    }

    public function it_has_type_meta(): void
    {
        $this->getMeta()->shouldBeLike(new TypeMeta());
    }
}
