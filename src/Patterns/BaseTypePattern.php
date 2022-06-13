<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Patterns;


use ReflectionClass;


trait BaseTypePattern
{
    /**
     * @param string $name
     * @return string|array<string|static>|static|int|float|null
     */
    public function __get(string $name)
    {
        $matches = $this->findReflectionProperties();
        foreach ($matches as $match) {
            if ($match[2] === $name) {
                if (isset($this->{$name}) === false) {
                    if ($this->isArray($match[1])) {
                        $this->{$name} = [];
                    } else {
                        $this->{$name} = null;
                    }
                }

                return $this->{$name};
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @param string|array<string|static>|static|int|float|null $value
     */
    public function __set(string $name, $value): void
    {
        $matches = $this->findReflectionProperties();
        foreach ($matches as $match) {
            if ($match[2] === $name) {
                if ($this->isArray($match[1]) && is_array($value) === false) {
                    $value = [$value];
                }

                $this->{$name} = $value;
            }
        }
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }

        return substr($haystack, -$length) === $needle;
    }

    /**
     * @return string[]
     */
    private function findReflectionProperties(): array
    {
        $rc = new ReflectionClass($this);
        preg_match_all(
            '~^  [ \t*]*  @property(?:|-read)  [ \t]+  ([^\s$]+)  [ \t]+  \$  (\w+)  ()~mx',
            (string)$rc->getDocComment(), $matches, PREG_SET_ORDER
        );

        return $matches;
    }

    private function isArray(string $matchResult): bool
    {
        $types = explode('|', $matchResult);
        foreach ($types as $type) {
            if ($this->endsWith($type, '[]')) {
                return true;
            }
        }

        return false;
    }

}