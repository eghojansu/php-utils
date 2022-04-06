<?php

declare(strict_types=1);

namespace Ekok\Utils;

class File
{
    public static function getClassByScan(string $dir, int $max = 1): array
    {
        return static::getSymbolsByScan($dir, $max, T_CLASS);
    }

    public static function getSymbolsByScan(string $dir, int $max = 1, int ...$symbols): array
    {
        $fixed = Str::fixslash($dir);
        $pattern = false === strpos($fixed, '*') ? $fixed . '/*.php' : $fixed;

        return array_reduce(
            static::traverseGlob($pattern),
            static fn(array $classes, string $filepath) => array_merge(
                $classes,
                static::getSymbols($filepath, $max, ...$symbols),
            ),
            array(),
        );
    }

    public static function getSymbols(string $path, int $max = 0, int ...$symbols): array
    {
        if (!$symbols) {
            return array();
        }

        $tokens = token_get_all(file_get_contents($path));
        $founds = array();
        $ns = null;
        $ctr = 0;
        $count = count($tokens);
        $wants = count($symbols);
        $find = static function (int $token, int &$found) use ($tokens, $count) {
            for ($i = $found; $i < $count; $i++) {
                if ($token === $tokens[$i][0]) {
                    $found = $i + 1;

                    return true;
                }
            }

            return false;
        };

        for ($i = 1; $i < $count; $i++) {
            if ($find(T_NAMESPACE, $i) && $find(T_NAME_QUALIFIED, $i)) {
                $ns = $tokens[$i - 1][1];

                continue;
            }

            for ($j = 0; $j < $wants; $j++) {
                if ($find($symbols[$j], $i) && $find(T_STRING, $i) && 'self' !== $tokens[$i - 1][1]) {
                    $founds[] = $ns . '\\' . $tokens[$i - 1][1];
                    $ctr++;
                }

                if ($max > 0 && $max === $ctr) {
                    break(2);
                }
            }
        }

        return $founds;
    }

    public static function traverse(string $path, string $pattern = null, int $matchMode = null): \Iterator
    {
        $dir = new \RecursiveDirectoryIterator(
            Str::fixslash($path),
            \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO
            | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
        );
        $flat = new \RecursiveIteratorIterator($dir);

        return $pattern ? new \RegexIterator($flat, $pattern, $matchMode ?? \RegexIterator::MATCH) : $flat;
    }

    public static function traverseGlob(string $pattern, int $flags = 0): array
    {
        $find = Str::fixslash($pattern);
        $base = basename($find);

        return array_reduce(
            false === strpos($find, '/') ? array() : glob(dirname($find) . '/*', GLOB_ONLYDIR),
            static fn (array $files, string $dir) => array_merge(
                $files,
                static::traverseGlob($dir . '/' . $base, $flags),
            ),
            glob($find, $flags),
        );
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
