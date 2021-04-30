<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Wsdl;


use Zazimou\WsdlToPhp\Helpers\StringHelper;


class Element
{
    /** @var string */
    public $name;
    /** @var string */
    public $type;
    /** @var bool */
    public $nullable = false;
    /** @var bool */
    public $arrayable = false;
    /** @var int */
    public $minimumElements;
    /** @var int */
    public $maximumElements;

    public function __construct(string $name , string $type)
    {
        $this->name = $name;
        $this->type = StringHelper::removeXmlNs($type);
    }

    public function setMinMaxElements(?string $min = null, ?string $max = null): void
    {
        if ($min == '') {
            $this->minimumElements = 1;
        }
        if ($max == '') {
            $this->maximumElements = 1;
        }
        if (isset($min) && $min != "") {
            $this->minimumElements = (int)$min;
        }
        if (isset($max) && $max != "") {
            $this->maximumElements = $max == 'unbounded' ? 0 : (int)$max;
        }
        if ($this->minimumElements === 0 && $this->maximumElements !== 0) {
            $this->nullable = true;
        }
        if ($this->minimumElements === 0  && $this->maximumElements === 0 ) {
            $this->arrayable = true;
        }
    }
}