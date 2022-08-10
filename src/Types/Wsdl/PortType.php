<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Types\Wsdl;


class PortType
{
    /** @var string */
    public $name;
    /** @var string|null */
    public $requestType;
    /** @var string|null */
    public $responseType;
    /** @var string|null */
    public $description;

    /**
     * PortType constructor.
     * @param string      $name
     * @param string|null $requestType
     * @param string|null $responseType
     * @param string|null $description
     */
    public function __construct(string $name, ?string $requestType = null, ?string $responseType = null, ?string $description = null)
    {
        $this->name = $name;
        $this->requestType = $requestType;
        $this->responseType = $responseType;
        $this->description = $description;
    }


}