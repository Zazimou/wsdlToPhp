<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Wsdl;


use Zazimou\WsdlToPhp\Helpers\StringHelper;


class Messages
{
    /** @var string[] */
    public $messages;

    public function addMessage(?string $name = null, ?string $value = null): void
    {
        if (isset($name) && isset($value)) {
            $this->messages[$name] = StringHelper::removeXmlNs($value);
        }
    }

    public function getMessage(string $name): ?string
    {
        $name = StringHelper::removeXmlNs($name);
        if (isset($this->messages[$name])) {
            return $this->messages[$name];
        }

        return null;
    }
}