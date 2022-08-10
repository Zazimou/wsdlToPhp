<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp;


use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Printer as NettePrinter;


class Printer extends NettePrinter
{
    /**
     * @param Method            $method
     * @param PhpNamespace|null $namespace
     * @return string
     */
    public function printMethod(Method $method, PhpNamespace $namespace = null): string
    {
        $str = parent::printMethod($method, $namespace);
        $exploded = explode("\n", $str);
        $values = [];
        $firstLineTab = false;
        $firstBracet = false;
        foreach ($exploded as $key => $value) {
            if ($value == '{' && $firstBracet == false) {
                $firstLineTab = true;
                $firstBracet = true;
                $values[] = $value;
                continue;
            }
            if ($firstLineTab == true) {
                $values[] = $value;
                $firstLineTab = false;
                continue;
            }
            $val = str_replace("\t", '', $value);
            $values[] = $val;
        }
        $str = implode("\n", $values);

        return $str;
    }
}