<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Patterns;


use SoapHeader;


class SoapClientPattern
{
    /** @var string[] */
    private static $classmap;

    /**
     * @param string                                   $wsdl
     * @param array<string|int|array<string|int>>|null $options
     */
    public function __construct(string $wsdl, ?array $options = [])
    {
        self::$classmap = self::loadClassMap();
        $options = self::normalizeOptions($options);
//        parent::__construct($wsdl, $options);
    }

    /**
     * @param string                   $name
     * @param BaseType[]               $args
     * @param array<string|mixed> $options
     * @param string[]|SoapHeader $inputHeaders
     * @param string[]|SoapHeader $outputHeaders
     * @return mixed
     */
    public function __soapCall(string $name, array $args, ?array $options = null, $inputHeaders = null, &$outputHeaders = null)
    {
        $args[0] = $args[0]->regenerateSoapArgs();
//      return parent::__soapCall($name, $args, $options, $inputHeaders, $outputHeaders);
    }

    public function callMethod(TypeHint $arguments): ReturnType
    {
        /** @var ReturnType $response */
        $response = $this->__soapCall('callMethod', [$arguments]);

        return $response;
    }

    public function callMethodWithoutRequest(): ReturnType
    {
        /** @var ReturnType $response */
        $response = $this->__soapCall('callMethod', []);

        return $response;
    }

    /**
     * @param array<string|int|array<string|int>>|null $options
     * @return array<string|int|array<string|int>>
     */
    protected static function normalizeOptions(?array $options): array
    {
        $options['classmap'] = self::$classmap;

        return $options;
    }

}