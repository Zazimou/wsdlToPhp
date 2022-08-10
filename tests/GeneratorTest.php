<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Tests;


use Tester\TestCase;
use Zazimou\WsdlToPhp\Generator;
use Zazimou\WsdlToPhp\Options\GeneratorOptions;


require_once __DIR__.'/bootstrap.php';

class GeneratorTest extends TestCase
{
    public function testFromUrl(): void
    {
        $options = new GeneratorOptions;
        $options->namespace = 'Zazimou\Test\Soap';
        $options->soapClientClassName = 'TestSoap';
        Generator::fromUrl('http://www.thomas-bayer.com/axis2/services/BLZService?wsdl', $options);
    }

}

(new GeneratorTest)->run();