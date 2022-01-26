<?php

namespace Ekok\Utils;

class File
{
    public static function traverse(string $path, string $pattern = null, int $matchMode = null): \Iterator
    {
        $dir = new \RecursiveDirectoryIterator(
            Str::fixslashes($path),
            \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO
            | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
        );
        $flat = new \RecursiveIteratorIterator($dir);

        return $pattern ? new \RegexIterator($flat, $pattern, $matchMode ?? \RegexIterator::MATCH) : $flat;
    }

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
        return ($exists = is_file($file)) ? self::loadFile($data ?? array(), $file) : null;
    }

    private static function loadFile()
    {
        extract(func_get_arg(0));

        return require func_get_arg(1);
    }
}
