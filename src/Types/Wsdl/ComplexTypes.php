<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Wsdl;


class ComplexTypes
{
    /** @var ComplexType[] */
    public $types;

    public function addComplexType(ComplexType $type): void
    {
        $this->types[$type->name] = $type;
    }
}