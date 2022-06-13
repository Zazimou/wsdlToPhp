<?php declare(strict_types=1);


namespace Zazimou\WsdlToPhp\Helpers;


use Zazimou\WsdlToPhp\Options\GeneratorOptions;


class GeneratorHelper
{
    const NAMESPACE_SEPARATOR = '\\';

    public static function generateTypesNamespace(GeneratorOptions $options): string
    {
        return join('\\', [$options->namespace, $options->typesFolderName]);
    }

    public static function pathFromNamespace(?string $namespace = null): string
    {
        if ($namespace and mb_substr($namespace, -1) == self::NAMESPACE_SEPARATOR)
        {
            $count = mb_strlen($namespace);
            $namespace = mb_substr($namespace, 0, $count - 1);
        }
        if ($namespace and mb_substr($namespace, 0) == self::NAMESPACE_SEPARATOR)
        {
            $namespace = mb_substr($namespace, 1);
        }
        $path = $namespace;

        return TMP_DIR . self::NAMESPACE_SEPARATOR . 'generated' . self::NAMESPACE_SEPARATOR . $path;
    }

    public static function cleanupDocComments(string $docComment): string
    {
        $comment = str_replace(['/**', '*/', '* ', '  '], '', $docComment);
        return str_replace([' @'], '@', $comment);
    }
}