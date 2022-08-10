<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp;


use DOMDocument;
use DOMElement;
use DOMNodeList;
use Nette\Utils\Strings;
use Zazimou\WsdlToPhp\Helpers\StringHelper;
use Zazimou\WsdlToPhp\Types\Wsdl\ComplexType;
use Zazimou\WsdlToPhp\Types\Wsdl\ComplexTypes;
use Zazimou\WsdlToPhp\Types\Wsdl\Descriptions;
use Zazimou\WsdlToPhp\Types\Wsdl\Element;
use Zazimou\WsdlToPhp\Types\Wsdl\Elements;
use Zazimou\WsdlToPhp\Types\Wsdl\Messages;
use Zazimou\WsdlToPhp\Types\Wsdl\PortType;
use Zazimou\WsdlToPhp\Types\Wsdl\PortTypes;


class WsdlExctractor
{

    /** @var ComplexTypes */
    public $types;

    /** @var PortTypes */
    public $methods;

    /** @var string */
    public $defaultSoapClientName = 'GeneratedSoapClient';

    /** @var Elements */
    public $elements;

    public static function fromString(string $wsdl): WsdlExctractor
    {
        $instance = new self;
        $instance->generate($wsdl);

        return $instance;
    }

    /**
     * @param DOMElement        $domWsdl
     * @param string            $startTagName
     * @param Descriptions|null $descriptions
     * @return Descriptions
     */
    private function exctractDescriptions(DOMElement $domWsdl, string $startTagName = 'binding', ?Descriptions $descriptions = null): Descriptions
    {
        if ($descriptions === null) {
            $descriptions = new Descriptions;
        }
        $bindings = $domWsdl->getElementsByTagName($startTagName);
        /** @var DOMElement $binding */
        foreach ($bindings as $binding) {
            $operations = $binding->getElementsByTagName('operation');
            /** @var DOMElement $operation */
            foreach ($operations as $operation) {
                $documentations = $operation->getElementsByTagName('documentation');
                /** @var DOMElement $documentation */
                foreach ($documentations as $documentation) {
                    $descriptions->addDescription($operation->getAttribute('name'), $documentation->nodeValue);
                }
            }
        }

        return $descriptions;
    }

    /**
     * @param DOMElement $domWsdl
     * @return Messages
     */
    private function exctractMessages(DOMElement $domWsdl): Messages
    {
        $messages = new Messages;
        $messies = $domWsdl->getElementsByTagName('message');
        /** @var DOMElement $message */
        foreach ($messies as $message) {
            $parts = $message->getElementsByTagName('part');
            /** @var DOMElement $part */
            foreach ($parts as $part) {
                $messages->addMessage($message->getAttribute('name'), $part->getAttribute('element'));
            }
        }

        return $messages;
    }

    /**
     * @param DOMElement   $item
     * @param ComplexTypes $complexTypes
     */
    private function extractContentType(DOMElement $item, ComplexTypes $complexTypes): void
    {
        $type = new ComplexType($item->getAttribute('name'));
        $complexies = $item->getElementsByTagName('complexContent');
        /** @var DOMElement $complex */
        foreach ($complexies as $complex) {
            $extensions = $complex->getElementsByTagName('extension');
            /** @var DOMElement $extension */
            foreach ($extensions as $extension) {
                $this->extractSequenciesToComplexType($extension, $type);
                $type->setExtends(StringHelper::getNullIfEmpty($extension->getAttribute('base')));
            }
        }
        $this->extractSequenciesToComplexType($item, $type);
        $complexTypes->addComplexType($type);
    }

    /**
     * @param DOMNodeList $elm
     * @param ComplexType $type
     */
    private function extractElementsToComplexType(DOMNodeList $elm, ComplexType $type): void
    {
        /** @var DOMElement $el */
        foreach ($elm as $el) {
            $typeElement = new Element($el->getAttribute('name'), $el->getAttribute('type'));
            $typeElement->setMinMaxElements($el->getAttribute('minOccurs'), $el->getAttribute('maxOccurs'));
            $type->addElement($typeElement);
        }
    }

    /**
     * @param DOMElement $domWsdl
     * @return PortTypes
     */
    private function extractMethods(DOMElement $domWsdl): PortTypes
    {
        $descriptions = $this->exctractDescriptions($domWsdl);
        $descriptions = $this->exctractDescriptions($domWsdl, 'portType', $descriptions);
        $messages = $this->exctractMessages($domWsdl);
        $portTypes = new PortTypes;
        $childs = $domWsdl->getElementsByTagName('portType');
        /** @var DOMElement $child */
        foreach ($childs as $child) {
            $operations = $child->getElementsByTagName('operation');
            /** @var DOMElement $operation */
            foreach ($operations as $operation) {
                $request = $response = null;
                $inputs = $operation->getElementsByTagName('input');
                /** @var DOMElement $input */
                foreach ($inputs as $input) {
                    $request = $messages->getMessage($input->getAttribute('message'));
                }
                $outputs = $operation->getElementsByTagName('output');
                /** @var DOMElement $output */
                foreach ($outputs as $output) {
                    $response = $messages->getMessage($output->getAttribute('message'));
                }
                $portTypes->addPortType(new PortType($operation->getAttribute('name'), $request, $response, $descriptions->getDescription($operation->getAttribute('name'))));
            }
        }

        return $portTypes;
    }

    /**
     * @param DOMElement  $extension
     * @param ComplexType $type
     */
    private function extractSequenciesToComplexType(DOMElement $extension, ComplexType $type): void
    {
        $sequencies = $extension->getElementsByTagName('sequence');
        /** @var DOMElement $sequence */
        foreach ($sequencies as $sequence) {
            $elm = $sequence->getElementsByTagName('element');
            $this->extractElementsToComplexType($elm, $type);
        }
    }

    /**
     * @param DOMElement $domWsdl
     * @return ComplexTypes
     */
    private function extractTypes(DOMElement $domWsdl): ComplexTypes
    {
        $this->elements = new Elements;
            $elm = $domWsdl->getElementsByTagName('element');
            /** @var DOMElement $item */
            foreach ($elm as $item) {
                if ($item->getAttribute('type') === '') {
                    continue;
                }
                $this->elements->addElement($item->getAttribute('name'), $item->getAttribute('type'));
            }
        $childs = $domWsdl->getElementsByTagName('types');
        $schemas = [];
        /** @var DOMElement $child */
        foreach ($childs as $child) {
            $schemas = $child->getElementsByTagName('schema');
        }
        $complexTypes = new ComplexTypes;
        /** @var DOMElement $schema */
        foreach ($schemas as $schema) {
            $cT = $schema->getElementsByTagName('complexType');
            $elements = $schema->getElementsByTagName('element');
            $contentTypeInElements = [];
            /** @var DOMElement $element */
            foreach ($elements as $element) {
                $item = $element->getElementsByTagName('complexType');
                if ($item->count() > 0) {
                    $this->extractContentType($element, $complexTypes);
                }
            }
            /** @var DOMElement $item */
            foreach ($cT as $item) {
                if ($item->getAttribute('name') === '') {
                    continue;
                }
                $this->extractContentType($item, $complexTypes);
            }
        }

        return $complexTypes;
    }

    /**
     * @param DOMElement $domWsdl
     */
    private function extractDefaultClientName(DOMElement $domWsdl): void
    {
        $definitions = $domWsdl->getElementsByTagName('definitions');
        /** @var DOMElement $definition */
        foreach ($definitions as $definition) {
            $def = $definition->getAttribute('name');
        }
        if (isset($def)) {
            $this->defaultSoapClientName = Strings::endsWith($def, 'Client') ? $def : $def . 'Client';
        }
    }

    /**
     * @param string $wsdl
     */
    private function generate(string $wsdl): void
    {
        $doc = new DOMDocument;
        $doc->loadXML($wsdl);
        $domWsdl = $doc->documentElement;
        $this->types = $this->extractTypes($domWsdl);
        $this->methods = $this->extractMethods($domWsdl);
        $this->extractDefaultClientName($domWsdl);
    }

}