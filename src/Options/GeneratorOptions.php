<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Options;


use SoapClient;


class GeneratorOptions
{
    /**
     * @var string
     * Namespace of generated SoapClient
     */
    public $namespace = 'Soap';
    /**
     * @var string|null
     * Name of SoapClient class
     */
    public $soapClientClassName;
    /**
     * @var string
     * Folder name fore type objects
     */
    public $typesFolderName = 'Types';
    /**
     * @var string
     * Name of SoapClient class. This class should extend default PHP SoapClient
     */
    public $soapClientExtender = SoapClient::class;
    /** @var CurlOptions|null
     * Curl options needed if you generate this client with URL
     */
    public $curlOptions;
    /** @var string
     * Version of PHP for type declaration. If you write 7.4 - Library generates fully typed objects for PHP 7.4
     */
    public $phpVersion = '7.3';
    /** @var bool
     * If is this true, than all types properties are normalized (first character lowed, etc.)
     */
    public $normalizeNames = true;

}