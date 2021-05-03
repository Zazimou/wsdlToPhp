<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Patterns;


class SoapClientPattern
{
    /** @var string[] */
    private static $classmap;

    public function __construct(string $wsdl, ?array $options = [])
    {
        self::$classmap = self::loadClassMap();
        $options = self::normalizeOptions($options);
//        parent::__construct($wsdl, $options);
    }

    public function callMethod(TypeHint $arguments): ReturnType
    {
        return $this->__soapCall('callMethod', [$arguments]);
    }

    public function callMethodWithoutRequest(): ReturnType
    {
        return $this->__soapCall('callMethod', []);
    }


    protected static function normalizeOptions(?array $options): array
    {
        $options['classmap'] = self::$classmap;

        return $options;
    }

}