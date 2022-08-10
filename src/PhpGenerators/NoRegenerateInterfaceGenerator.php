<?php

namespace Zazimou\WsdlToPhp\PhpGenerators;

use Nette\PhpGenerator\ClassType;
use Zazimou\WsdlToPhp\Helpers\GeneratorHelper;
use Zazimou\WsdlToPhp\Options\GeneratorOptions;


class NoRegenerateInterfaceGenerator extends BasePhpGenerator
{
    public function __construct(GeneratorOptions $generatorOptions)
    {
        $this->namespace = $generatorOptions->namespace;
        $this->filePath = GeneratorHelper::pathFromNamespace($this->namespace);
        parent::__construct($generatorOptions);
    }

    public function createClass(): void
    {
        $phpFile = $this->createFile();
        $className = 'NoRegenerate';
        $phpNamespace = $phpFile->getNamespaces()[$this->namespace];
        $class = new ClassType($className);
        $class->setType('interface');
        $phpNamespace->add($class);
        $this->printClass($className, $phpFile);
    }

}