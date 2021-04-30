<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Wsdl;


use Zazimou\WsdlToPhp\Helpers\StringHelper;


class Descriptions
{
    /** @var string[] */
    public $descriptions;

    public function addDescription(?string $name = null, ?string $value = null): void
    {
        if (isset($name) && isset($value)) {
            $this->descriptions[$name] = StringHelper::removeWhiteChars($value);
        }
    }

    public function getDescription(string $name): ?string
    {
        if (isset($this->descriptions[$name])) {
            return $this->descriptions[$name];
        }

        return null;
    }
}