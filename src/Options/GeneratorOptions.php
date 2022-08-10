<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Options;


use DateTime;
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

    /**
     * @var string
     * Name of DateTime class. This class should extend default PHP DateTime
     */
    public $dateTimeClassExtender = DateTime::class;

    /** @var CurlOptions|null
     * Curl options needed if you generate this client with URL
     */
    public $curlOptions;

    /** @var string
     * Version of PHP for type declaration. If you write 7.4 - Library generates fully typed objects for PHP 7.4
     */
    public $phpVersion = '7.4';

    /** @var bool
     * If true, generator generates constants for usage od property names
     */
    public bool $generateConstants = true;
    /** @var string
     * Property constants prefix
     */
    public string $constantsPrefix = 'TAG_';

}