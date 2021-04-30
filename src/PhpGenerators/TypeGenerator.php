<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\PhpGenerators;


use DateTime;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Property;
use Nette\Utils\Strings;
use Zazimou\WsdlToPhp\Exceptions\UnexpectedValueException;
use Zazimou\WsdlToPhp\Helpers\GeneratorHelper;
use Zazimou\WsdlToPhp\Options\GeneratorOptions;
use Zazimou\WsdlToPhp\Types\Generator\RenamedProperties;
use Zazimou\WsdlToPhp\Types\Wsdl\ComplexType;
use Zazimou\WsdlToPhp\Types\Wsdl\ComplexTypes;
use Zazimou\WsdlToPhp\Types\Wsdl\Element;


class TypeGenerator extends BasePhpGenerator
{
    /** @var ComplexTypes */
    public $types;
    /** @var ClassType[] */
    public $classes;
    /** @var RenamedProperties */
    public $renamedProperties;
    /** @var string[] */
    public $classmap;

    /**
     * TypeGenerator constructor.
     * @param GeneratorOptions $generatorOptions
     * @param ComplexTypes     $types
     */
    public function __construct(GeneratorOptions $generatorOptions, ComplexTypes $types)
    {
        $this->namespace = GeneratorHelper::generateTypesNamespace($generatorOptions);
        $this->filePath = GeneratorHelper::pathFromNamespace($this->namespace);
        $this->types = $types;
        $this->renamedProperties = new RenamedProperties;
        parent::__construct($generatorOptions);
    }

    /**
     * @param ComplexType $type
     * @throws UnexpectedValueException
     */
    public function createClass(ComplexType $type): void
    {
        $phpFile = $this->createFile();
        $phpNamespace = $phpFile->getNamespaces()[$this->namespace];
        $class = $phpNamespace->addClass($type->name);
        $this->classmap[$type->name] = $this->namespace.'\\'.$type->name;
        $properties = [];
        foreach ($type->elements as $element) {
            if ($element->type == 'dateTime') {
                $phpNamespace->addUse(DateTime::class);
            }
            $property = new Property($this->normalizePropertyName($element));
            $this->resolvePropertyTypeByPhpVersion($property, $element);
            if ($element->maximumElements > 1) {
                $property->addComment(sprintf('Maximum of elements in object is %s', $element->maximumElements));
            }
            $docComment = $this->normalizePropertyDocComment($element);
            if (is_string($docComment)) {
                $property->addComment($docComment);
            }
            if ($element->nullable) {
                $property->setNullable();
            }
            $properties[] = $property;
        }
        $class->setProperties($properties);
        if (isset($type->extends)) {
            $class->setExtends($this->namespace.'\\'.$type->extends);
        }
        $this->printClass($type->name, $phpFile);
    }

    /**
     * @param Element $element
     * @return string
     */
    protected function normalizePropertyName(Element $element): string
    {
        $normalizedName = $element->name;
        if (Strings::contains($normalizedName, '-')) {
            $explode = explode('-', $normalizedName);
            $uper = [];
            foreach ($explode as $key => $ex) {
                $uper[] = Strings::firstUpper($ex);
            }
            $normalizedName = implode('', $uper);
        }
        if ($this->options->normalizeNames === true) {
            $normalizedName = Strings::firstLower($normalizedName);
        }
        if ($element->name != $normalizedName) {
            $this->renamedProperties->addProperty($element->name, $normalizedName);
        }

        return $normalizedName;
    }


}