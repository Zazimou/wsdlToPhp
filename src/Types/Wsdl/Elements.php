<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Wsdl;


use Zazimou\WsdlToPhp\Helpers\StringHelper;


class Elements
{
    /** @var Element[] */
    public $elements = [];

    public function addElement(string $name, string $type): void
    {
        $type = StringHelper::removeXmlNs($type);
        $this->elements[$name] = new Element($name, $type);
    }

    public function getElement(string $name): ?Element
    {
        return isset($this->elements[$name]) ? $this->elements[$name] : null;
    }
}