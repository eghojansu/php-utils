<?php

declare(strict_types=1);

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

    public static function load(
        string $file,
        array $data = null,
        bool $safe = true,
        string &$output = null,
    ) {
        list($result, $output, $error) = file_exists($file) ? static::safeLoad(
            $data ?? array(),
            $file,
            ob_get_level(),
        ) : array(null, null, new \LogicException(sprintf('File not found: %s', $file)));

        if ($error && !$safe) {
            throw $error;
        }

        return $result;
    }

    private static function safeLoad(): array
    {
        try {
            ob_start();
            extract(func_get_arg(0)); // data
            $result = require func_get_arg(1); // file
            $output = ob_get_clean();

            return array($result, $output, null);
        } catch (\Throwable $error) {
            while (ob_get_level() > func_get_arg(2)) { // ob level
                ob_end_clean();
            }

            return array(null, null, new \RuntimeException(sprintf(
                'Error in file: %s (%s)',
                func_get_arg(1),
                $error->getMessage(),
            ), 0, $error));
        }
    }
}
