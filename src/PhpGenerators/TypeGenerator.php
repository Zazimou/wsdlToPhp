<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\PhpGenerators;


use DateTime;
use Nette\PhpGenerator\ClassType;
use Nette\Utils\Strings;
use Zazimou\WsdlToPhp\Exceptions\UnexpectedValueException;
use Zazimou\WsdlToPhp\Helpers\GeneratorHelper;
use Zazimou\WsdlToPhp\Options\GeneratorOptions;
use Zazimou\WsdlToPhp\Types\Wsdl\ComplexType;
use Zazimou\WsdlToPhp\Types\Wsdl\ComplexTypes;
use Zazimou\WsdlToPhp\Types\Wsdl\Element;


class TypeGenerator extends BasePhpGenerator
{
    /** @var ComplexTypes */
    public $types;
    /** @var ClassType[] */
    public $classes;
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
        $class->addTrait($this->namespace.'\BaseType');
        foreach ($type->elements as $element) {
            if ($element->type === 'dateTime') {
                $phpNamespace->addUse(DateTime::class);
            }
            $comments = [];
            if ($element->maximumElements > 1) {
                $comments[] = sprintf('Maximum of elements in object is %s', $element->maximumElements);
            }
            $docComment = $this->normalizePropertyDocComment($element);
            if (is_string($docComment)) {
                $comments[] = $docComment;
            }
            $class->addComment(sprintf('@property %s $%s %s', $this->normalizePropertyType($element), $this->normalizePropertyName($element), join('| ', $comments)));
        }
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
            foreach ($explode as $ex) {
                $uper[] = Strings::firstUpper($ex);
            }
            $normalizedName = implode('', $uper);
        }

        return $normalizedName;
    }


}