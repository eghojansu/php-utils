<?php

namespace Ekok\Utils;

class Str
{
    public static function fixslashes(string $str): string
    {
        return preg_replace(
            '/\x00+/',
            '/',
            strtr($str, array_fill_keys(array('/', '\\'), chr(0))),
        );
    }

    public static function split(string $str, string $symbols = null): array
    {
        return preg_split('/[' . ($symbols ?? ',;|') . ']/i', $str, 0, PREG_SPLIT_NO_EMPTY);
    }

    public static function quote(string $text, string|array $quote = null, string $split = null): string
    {
        $open = $quote[0] ?? '"';
        $close = $quote[1] ?? $open;

        return $open . (
            $split ?
                str_replace($split, $close . $split . $open, $text) :
                $text
        ) . $close;
    }

    public static function caseCamel(string $text): string
    {
        return str_replace('_', '', lcfirst(ucwords($text, '_')));
    }

    public static function caseSnake(string $text): string
    {
        return strtolower(preg_replace('/\p{Lu}/', '_$0', lcfirst($text)));
    }

    public static function casePascal(string $text): string
    {
        return ucfirst(static::caseCamel($text));
    }

    public static function caseTitle(string $text): string
    {
        return ucwords(preg_replace('/\h+/', ' ', str_replace(array('_', '-'), ' ', static::caseSnake($text))));
    }

    public static function className(string $fns, bool $snake = false): string
    {
        $className = ltrim(strrchr('\\' . $fns, '\\'), '\\');

        return $snake ? static::caseSnake($className) : $className;
    }

    public static function random(int $len = 8, bool $lower = true, string $salt = null): string
    {
        $uid = '';
        $min = max(4, min(128, $len));
        $pattern = $lower ? "#(*UTF8)[^A-Za-z0-9]#" : "#(*UTF8)[^A-Z0-9]#";
        $saltiness = $salt ?? bin2hex(random_bytes($len));

        do {
            $hex = md5($saltiness . uniqid('', true));
            $pck = pack('H*', $hex);
            $tmp = base64_encode($pck);
            $uid = preg_replace($pattern, '', $tmp);
        } while (strlen($uid) < $min);

        return substr($uid, 0, $min);
    }

    public static function startsWith(string $str, string ...$prefixes): string|null
    {
        return Arr::some($prefixes, static fn($prefix) => str_starts_with($str, $prefix), $match) ? $match[1] : null;
    }

    public static function endsWith(string $str, string ...$suffixes): string|null
    {
        return Arr::some($suffixes, static fn($suffix) => str_ends_with($str, $suffix), $match) ? $match[1] : null;
    }
}
