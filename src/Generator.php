<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp;


use ReflectionException;
use Zazimou\WsdlToPhp\Exceptions\CurlException;
use Zazimou\WsdlToPhp\Exceptions\UnexpectedValueException;
use Zazimou\WsdlToPhp\Options\GeneratorOptions;
use Zazimou\WsdlToPhp\PhpGenerators\BaseTypeGenerator;
use Zazimou\WsdlToPhp\PhpGenerators\SoapClassGenerator;
use Zazimou\WsdlToPhp\PhpGenerators\TypeGenerator;


class Generator
{
    /** @var WsdlExctractor */
    public $exctractor;
    /** @var GeneratorOptions */
    public $options;

    /**
     * @param string           $wsdl
     * @param GeneratorOptions $options
     * @throws ReflectionException
     * @throws UnexpectedValueException
     */
    public static function fromString(string $wsdl, GeneratorOptions $options): void
    {
        $instance = new self;
        $instance->options = $options;
        $instance->exctractor = WsdlExctractor::fromString($wsdl);
        $instance->generateFiles();
    }

    /**
     * @param string           $url
     * @param GeneratorOptions $options
     * @throws CurlException
     * @throws ReflectionException
     * @throws UnexpectedValueException
     */
    public static function fromUrl(string $url, GeneratorOptions $options): void
    {
        $wsdlString = (new Curl($url, $options->curlOptions))->download();
        self::fromString($wsdlString, $options);
    }

    /**
     * @throws UnexpectedValueException
     * @throws ReflectionException
     */
    private function generateFiles(): void
    {
        $baseTypeGenerator = new BaseTypeGenerator($this->options);
        $typesGenerator = new TypeGenerator($this->options, $this->exctractor->types);
        $soapClassGenerator = new SoapClassGenerator($this->options);
        $baseTypeGenerator->createClass();
        foreach ($this->exctractor->types->types as $type) {
            $typesGenerator->createClass($type);
        }
        $soapClassGenerator->createClass($this->exctractor->methods, $this->exctractor->defaultSoapClientName, $typesGenerator->classmap, $typesGenerator->namespace, $this->exctractor->elements);
    }
}