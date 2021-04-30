<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Wsdl;


use Zazimou\WsdlToPhp\Helpers\StringHelper;


class ComplexType
{
    /** @var string */
    public $name;

    /** @var Element[] */
    public $elements = [];

    /** @var string */
    public $extends;

    /**
     * ComplexType constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addElement(Element $element): void
    {
        $this->elements[$element->name] = $element;
    }

    /**
     * @param string $extends
     */
    public function setExtends(?string $extends): void
    {
        if (isset($extends)) {
            $this->extends = StringHelper::removeXmlNs($extends);
        }
    }


}