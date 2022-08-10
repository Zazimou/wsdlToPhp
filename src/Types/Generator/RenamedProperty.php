<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Generator;


class RenamedProperty
{
    /** @var string */
    public $original;
    /** @var string */
    public $renamed;

    public function __construct(string $original, string $renamed)
    {
        $this->original = $original;
        $this->renamed = $renamed;
    }


}