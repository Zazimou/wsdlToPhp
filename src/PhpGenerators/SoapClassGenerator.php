<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\PhpGenerators;


use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\Property;
use Nette\Utils\Strings;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionType;
use Zazimou\WsdlToPhp\Exceptions\UnexpectedValueException;
use Zazimou\WsdlToPhp\Helpers\GeneratorHelper;
use Zazimou\WsdlToPhp\Options\GeneratorOptions;
use Zazimou\WsdlToPhp\Patterns\SoapClientPattern;
use Zazimou\WsdlToPhp\Types\Generator\RenamedProperties;
use Zazimou\WsdlToPhp\Types\Wsdl\Element;
use Zazimou\WsdlToPhp\Types\Wsdl\Elements;
use Zazimou\WsdlToPhp\Types\Wsdl\PortType;
use Zazimou\WsdlToPhp\Types\Wsdl\PortTypes;


class SoapClassGenerator extends BasePhpGenerator
{

    public function __construct(GeneratorOptions $generatorOptions)
    {
        $this->namespace = $generatorOptions->namespace;
        $this->filePath = GeneratorHelper::pathFromNamespace($this->namespace);
        parent::__construct($generatorOptions);
    }

    /**
     * @param PortTypes         $portTypes
     * @param string            $defaultClassName
     * @param array             $classmap
     * @param RenamedProperties $renamedProperties
     * @param string            $typesNamespace
     * @param Elements          $elements
     * @throws ReflectionException
     * @throws UnexpectedValueException
     */
    public function createClass(PortTypes $portTypes, string $defaultClassName, array $classmap, RenamedProperties $renamedProperties, string $typesNamespace, Elements $elements): void
    {
        $phpFile = $this->createFile();
        $className = $this->validateClassName($defaultClassName);
        $phpNamespace = $phpFile->getNamespaces()[$this->namespace];
        $pattern = ClassType::from(SoapClientPattern::class);
        $class = new ClassType($className);
        $phpNamespace->addUse($typesNamespace);
        $phpNamespace->addUse($this->options->soapClientExtender);
        $phpNamespace->add($class);
        $class->setExtends($this->options->soapClientExtender);
        $properies = [];
        $elementRenamedProperies = new Element('renamedProperties', 'string');
        $elementRenamedProperies->arrayable = true;
        $propertyRenamedProperties = new Property('renamedProperties');
        $propertyRenamedProperties->setStatic();
        $this->resolvePropertyTypeByPhpVersion($propertyRenamedProperties, $elementRenamedProperies);
        $properies[] = $propertyRenamedProperties;
        $elementClassmap = new Element('classmap', 'string');
        $elementClassmap->arrayable = true;
        $propertyClassMap = new Property('classmap');
        $propertyClassMap->setStatic();
        $this->resolvePropertyTypeByPhpVersion($propertyClassMap, $elementClassmap);
        $properies[] = $propertyClassMap;
        $methods = $this->getPatternMethods();
        foreach ($portTypes->types as $method) {
            $methodName = $this->normalizeMethodName($method);
            if (isset($method->requestType)) {
                $reflection = new ReflectionMethod(SoapClientPattern::class, 'callMethod');
                $callMethod = $pattern->getMethod('callMethod');
                $param = new Parameter('arguments');
                $paramTypeName = $elements->getElement($method->requestType) !== null ? $elements->getElement($method->requestType)->type : $method->requestType;
            } else {
                $reflection = new ReflectionMethod(SoapClientPattern::class, 'callMethodWithoutRequest');
                $callMethod = $pattern->getMethod('callMethodWithoutRequest');
            }
            $newMethod = $callMethod->cloneWithName($methodName);
            if (isset($param, $paramTypeName)) {
                $paramTypeNamespace = $typesNamespace.'\\'.$paramTypeName;
                $phpNamespace->addUse($paramTypeNamespace);
                $newMethod->addComment('@param '.$paramTypeName.' $arguments');
                $param->setType($paramTypeNamespace);
                $newMethod->setParameters([$param]);
            }
            $body = $this->getMethodBody($reflection);
            $body = str_replace('callMethod', $method->name, $body);
            $newMethod->setBody($body);
            if (isset($method->description)) {
                $newMethod->addComment($method->description);
            }
            if (isset($method->responseType)) {
                if ($elements->getElement($method->responseType) !== null) {
                    $returnTypeName = $elements->getElement($method->responseType)->type;
                } else {
                    $returnTypeName = $method->responseType;
                }
                if (isset($returnTypeName)) {
                    $returnTypeNamespace = $typesNamespace.'\\'.$returnTypeName;
                    $phpNamespace->addUse($returnTypeNamespace);
                    $newMethod->setReturnType($returnTypeNamespace);
                    $newMethod->addComment('@return '.$returnTypeName);
                }
            }
            $methods[] = clone $newMethod;
        }
        $mapValues = [];
        foreach ($classmap as $key => $value) {
            $mapValues[$key] = '    "'.$key.'" => "'.$value.'"';
        }
        sort($mapValues);
        $methods[] = $this->addMethodWithArrayBody('loadClassMap', $mapValues);
        $renamedValues = [];
        foreach ($renamedProperties->properties as $key => $value) {
            $renamedValues[$value->renamed] = '    "'.$value->renamed.'" => "'.$value->original.'"';
        }
        sort($renamedValues);
        $methods[] = $this->addMethodWithArrayBody('loadRenamedProperties', $renamedValues);
        $class->setMethods($methods);
        $class->setProperties($properies);

        $this->printClass($className, $phpFile);
    }

    public static function getVisibilityFromReflection(ReflectionMethod $reflection): string
    {
        if ($reflection->isPublic() === true) {
            return 'public';
        }
        if ($reflection->isProtected() === true) {
            return 'protected';
        }
        if ($reflection->isPrivate() === true) {
            return 'private';
        }

        return 'public';
    }

    protected function normalizeMethodName(PortType $method): string
    {
        $normalizedName = $method->name;
        if (Strings::contains($normalizedName, '-')) {
            $explode = explode('-', $normalizedName);
            $uper = [];
            foreach ($explode as $key => $ex) {
                $uper[] = Strings::firstUpper($ex);
            }
            $normalizedName = implode('', $uper);
        }
        $normalizedName = Strings::firstLower($normalizedName);

        return $normalizedName;
    }

    private function addMethodWithArrayBody(string $methodName, array $values): Method
    {
        $string = implode(",\n", $values);
        $method = new Method($methodName);
        $method->setVisibility('protected')->setStatic()->setBody("return [\n".$string."\n];");

        return $method;
    }

    /**
     * @param ReflectionMethod $method
     * @return string
     */
    private function getMethodBody(ReflectionMethod $method): string
    {
        $fileName = $method->getFileName();
        $startLine = $method->getStartLine() + 1;
        $endLine = $method->getEndLine() - 1;

        $source = file($fileName);
        $source = implode('', array_slice($source, 0, count($source)));
        $source = preg_split("/".PHP_EOL."/", $source);

        $body = '';
        for ($i = $startLine; $i < $endLine; $i++) {
            $beforeSubstr = "{$source[$i]}\n";
            $length = Strings::length($beforeSubstr);
            $afterSubstr = mb_substr($beforeSubstr, 4, $length - 4);
            if ($afterSubstr == '') {
                $afterSubstr = "\n";
            }
            $body .= ($afterSubstr);
        }

        return $body;
    }

    /**
     * @throws ReflectionException
     * @throws ReflectionException
     */
    private function getPatternMethods(): array
    {
        $createdMethods = [];
        $skippedMethods = ['callMethod', 'callMethodWithoutRequest'];
        $patternClass = new ReflectionClass(SoapClientPattern::class);
        $methods = $patternClass->getMethods();
        /** @var ReflectionMethod $method */
        foreach ($methods as $method) {
            if (in_array($method->name, $skippedMethods)) {
                continue;
            }
            $info = new ReflectionMethod(SoapClientPattern::class, $method->name);
            $item = new Method($info->name);
            $visibility = self::getVisibilityFromReflection($info);
            $item->setVisibility($visibility);
            $item->setStatic($info->isStatic());
            //            $item->setReturnType($info->getReturnType());
            $returnType = $info->getReturnType();
            if (!empty($returnType)) {
                $item->setReturnType($info->getReturnType()->getName());
                $item->setReturnNullable($returnType->allowsNull());
            }
            if ($info->getDocComment()) {
                $item->setComment($info->getDocComment());
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

    private function validateClassName(string $defaultClassName): string
    {
        if (isset($this->options->soapClientClassName)) {
            return $this->options->soapClientClassName;
        }

        return $defaultClassName;
    }

}