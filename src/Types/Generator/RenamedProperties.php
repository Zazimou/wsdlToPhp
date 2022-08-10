<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Generator;


class RenamedProperties
{
    /** @var RenamedProperty[] */
    public $properties = [];

    public function addProperty(string $original, string $renamed): void
    {
        $this->properties[$original] = new RenamedProperty($original, $renamed);
    }
}