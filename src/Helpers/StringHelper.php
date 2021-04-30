<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Helpers;


class StringHelper
{
    public static function getNullIfEmpty(?string $value = null): ?string
    {
        if ($value == '') {
            return null;
        }

        return $value;
    }

    public static function removeWhiteChars(?string $value = null): ?string
    {
        if (isset($value)) {
            $value = str_replace(["\n", "\r", "\t"], '', $value);
            $value = preg_replace('/\s+/', ' ', $value);
        }

        return trim($value);
    }

    public static function removeXmlNs(string $value): string
    {
        $values = explode(':', $value);
        $value = count($values) > 1 ? $values[1] : $value;

        return $value;
    }
}