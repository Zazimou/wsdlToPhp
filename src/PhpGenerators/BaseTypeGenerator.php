<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\PhpGenerators;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\Property;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionType;
use Zazimou\WsdlToPhp\Helpers\GeneratorHelper;
use Zazimou\WsdlToPhp\Options\GeneratorOptions;
use Zazimou\WsdlToPhp\Patterns\BaseTypePattern;


class BaseTypeGenerator extends BasePhpGenerator
{
    public function __construct(GeneratorOptions $generatorOptions)
    {
        $this->namespace = GeneratorHelper::generateTypesNamespace($generatorOptions);
        $this->filePath = GeneratorHelper::pathFromNamespace($this->namespace);
        parent::__construct($generatorOptions);
    }

    public function createClass(): void
    {
        $phpFile = $this->createFile();
        $className = 'BaseType';
        $phpNamespace = $phpFile->getNamespaces()[$this->namespace];
        $class = new ClassType($className);
        $class->setType('class');
        $class->setAbstract();
        $phpNamespace->add($class);
        $phpNamespace->addUse('ReflectionClass');
        $phpNamespace->addUse('DateTime');
        $props[] = $this->createDataProp();
        $props[] = $this->createDateFormatsProp();
        $class->setProperties($props);
        $methods = $this->getPatternMethods();
        $class->setMethods($methods);

        $this->printClass($className, $phpFile);
    }

    private function createDataProp(): Property
    {
        $property = new Property('data');
        $property->setType('array');
        $property->setValue([]);
        $property->setPrivate();
        $property->setComment('@var array<string|array<string|static>|static|int|float|null|bool|DateTime>');
        return $property;
    }

    private function createDateFormatsProp(): Property
    {
        $property = new Property('dateFormats');
        $property->setType('array');
        $property->setValue([]);
        $property->setPrivate();
        $property->setComment('@var array<int|string, string>');
        return $property;
    }

    /**
     * @throws ReflectionException
     * @throws ReflectionException
     */
    private function getPatternMethods(): array
    {
        $createdMethods = [];
        $patternClass = new ReflectionClass(BaseTypePattern::class);
        $methods = $patternClass->getMethods();
        foreach ($methods as $method) {
            $info = new ReflectionMethod(BaseTypePattern::class, $method->name);
            $item = new Method($info->name);
            $visibility = self::getVisibilityFromReflection($info);
            $item->setVisibility($visibility);
            $item->setStatic($info->isStatic());
            $returnType = $info->getReturnType();
            if (!empty($returnType)) {
                $item->setReturnType($info->getReturnType()->getName());
                $item->setReturnNullable($returnType->allowsNull());
            }
            if ($info->getDocComment()) {
                $item->setComment(GeneratorHelper::cleanupDocComments($info->getDocComment()));
            }
            $params = $info->getParameters();
            $parameters = [];
            if (!empty($params)) {
                foreach ($params as $param) {
                    $parameter = new Parameter($param->getName());
                    /** @var ReflectionType $type */
                    $type = $param->getType();
                    if (!empty($type)) {
                        $parameter->setType($type->getName());
                        $parameter->setNullable($type->allowsNull());
                    }
                    if ($param->isDefaultValueAvailable()) {
                        $parameter->setDefaultValue($param->getDefaultValue());
                    }
                    $parameters[] = $parameter;
                }
            }
            $item->setParameters($parameters);
            $item->body = $this->getMethodBody($info);
            $createdMethods[] = $item;
        }

        return $createdMethods;
    }

}