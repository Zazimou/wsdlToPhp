<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Patterns;


use DateTime;
use ReflectionClass;


class BaseTypePattern
{
    /** @var string|array<string|static>|static|int|float|null */
    private array $data = [];

    /** @var string[] */
    private array $dateFormats = [];

    /**
     * @param string $name
     * @return string|array<string|static>|static|int|float|null
     */
    public function __get(string $name)
    {
        $matches = $this->findReflectionProperties();
        foreach ($matches as $match) {
            if ($match[2] === $name) {
                if (isset($this->data[$name]) === false) {
                    if ($this->isArray($match[1])) {
                        $this->data[$name] = [];
                    } else {
                        $this->data[$name] = null;
                    }
                }

                return $this->data[$name];
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
                $value = $this->retypeValue($value, $match[1]);
                $this->data[$name] = $value;
                break;
            }
        }
    }

    /**
     * @param array|bool|DateTime|float|int|string|null       $value
     * @param string $types
     * @return array|bool|DateTime|float|int|string|null
     */
    private function retypeValue($value, string $types)
    {
        $types = $this->getArrayOfTypes($types);
        foreach ($types as $type) {
            if ($this->isArray($type)) {
                return is_array($value) ? $value : [$value];
            }
            if ($type === 'string') {
                return (string) $value;
            } elseif ($type === 'int') {
                return (int) $value;
            } elseif ($type === 'float') {
                return (float) $value;
            } elseif ($type === 'bool') {
                return (bool) $value;
            } elseif ($type === 'DateTime') {
                return $value instanceof DateTime ? $value : (is_string($value) ? new DateTime($value) : null);
            } elseif ($type === 'array') {
                return is_array($value) ? $value : [];
            } elseif ($type === 'object') {
                return is_object($value) ? $value : null;
            } elseif ($type === 'null') {
                return $value === null ? null : $value;
            }else{
                return $value;
            }
        }

        return $value;
    }

    private function getArrayOfTypes(string $type): array
    {
        return explode('|', $type);
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
        $comments = $this->findDocComment($rc);
        $retMatches = [];
        foreach ($comments as $comment) {
            preg_match_all(
                '~^  [ \t*]*  @property(?:|-read)  [ \t]+  ([^\s$]+)  [ \t]+  \$  (\w+)  ()~mx',
                $comment, $matches, PREG_SET_ORDER
            );
            $retMatches = array_merge($retMatches, $matches);
        }

        return $retMatches;
    }

    /**
     * @param ReflectionClass<static> $reflection
     * @param string[]           $comments
     * @return string[]
     */
    private function findDocComment(ReflectionClass $reflection, array $comments = []): array
    {
        $comment = $reflection->getDocComment();
        if ($comment !== false) {
            $comments[] = $comment;
        }
        $parentClass = $reflection->getParentClass();
        if ($parentClass) {
            $comments = $comments + $this->findDocComment($parentClass, $comments);
        }

        return $comments;
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

    /**
     * @internal
     * @return array<string|array<string|static>|static|int|float|null>
     */
    public function regenerateSoapArgs(): array
    {
        $args = [];
        $matches = $this->findReflectionProperties();
        foreach ($matches as $match) {
            if (isset($this->data[$match[2]])) {
                $data = $this->data[$match[2]];
                if ($data instanceof DateTime) {
                    if (isset($this->dateFormats[0])) {
                        $args[$match[2]] = $data->format($this->dateFormats[0]);
                        continue;
                    }
                    if (isset($this->dateFormats[$match[2]])) {
                        $args[$match[2]] = $data->format($this->dateFormats[$match[2]]);
                        continue;
                    }
                    $args[$match[2]] = $data->format('c');
                    continue;
                }
                if ($data instanceof self) {
                    $args[$match[2]] = $data->regenerateSoapArgs();
                    continue;
                }
                $args[$match[2]] = $data;
            }
        }

        return $args;
    }

    /**
     * @param string      $dateFormat
     * @param string|null $propName
     */
    public function addDateFormat(string $dateFormat, string $propName = null): void
    {
        if ($propName === null) {
            $this->dateFormats = [$dateFormat];
        } else {
            $this->dateFormats[$propName] = $dateFormat;
        }
    }



}