<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Patterns;


class SoapClientPattern
{
    /** @var string[] */
    private static $classmap;

    /**
     * @param string     $wsdl
     * @param array<string|int|array<string|int>>|null $options
     */
    public function __construct(string $wsdl, ?array $options = [])
    {
        self::$classmap = self::loadClassMap();
        $options = self::normalizeOptions($options);
//        parent::__construct($wsdl, $options);
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