<?php

namespace Ekok\Utils;

class File
{
    public static function touch(string $path, string $content = null, int $permissions = 0775): bool
    {
        static::mkdir(dirname($path));

        return null === $content ? touch($path) : (bool) file_put_contents($path, $content);
    }

    public static function mkdir(string $path, bool $recursive = true, int $permissions = 0775): bool
    {
        return is_dir($path) ? true : mkdir($path, $permissions, $recursive);
    }

    public static function load(string $file, array $data = null, bool &$exists = null)
    {
        if ($exists = is_file($file)) {
            return (static function () {
                extract(func_get_arg(0));

                return require func_get_arg(1);
            })($data ?? array(), $file);
        }

        return null;
    }
}
