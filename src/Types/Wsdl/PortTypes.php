<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Wsdl;


class PortTypes
{
    /** @var PortType[] */
    public $types;

    public function addPortType(PortType $type): void
    {
        $this->types[$type->name] = $type;
    }
}