<?php

namespace Ekok\Utils;

class Str
{
    public static function fixslashes(string $str): string
    {
        return preg_replace('#[/\\]+#', '/', $str);
    }

    public static function split(string $str, string $symbols = ',;|'): array
    {
        return preg_split('/[' . $symbols . ']/i', $str, 0, PREG_SPLIT_NO_EMPTY);
    }

    public static function quote(string $text, string $open = '"', string $close = null, string $delimiter = '.'): string
    {
        $a = $open;
        $b = $close ?? $a;

        return $a . str_replace($delimiter, $b . $delimiter . $a, $text) . $b;
    }

    public static function caseCamel(string $text): string
    {
        return str_replace('_', '', lcfirst(ucwords($text, '_')));
    }

    public static function caseSnake(string $text): string
    {
        return strtolower(preg_replace('/\p{Lu}/', '_$0', lcfirst($text)));
    }

    public static function random(int $len = 8, string $salt = null): string
    {
        $min = max(4, min(128, $len));
        $saltiness = $salt ?? bin2hex(random_bytes($len));

        do {
            $hex = md5($saltiness . uniqid('', true));
            $pack = pack('H*', $hex);
            $tmp = base64_encode($pack);
            $uid = preg_replace("#(*UTF8)[^A-Za-z0-9]#", '', $tmp);
        } while (strlen($uid) < $min);

        return substr($uid, 0, $min);
    }

    public static function random_up(int $len = 8, string $salt = null): string
    {
        return strtoupper(random($len, $salt));
    }

    public static function cast(string $value): int|float|bool|string|array|null
    {
        $val = trim($value);

        if (preg_match('/^(?:0x[0-9a-f]+|0[0-7]+|0b[01]+)$/i', $val)) {
            return intval($val, 0);
        }

        if (is_numeric($val)) {
            return $val * 1;
        }

        if (preg_match('/^\w+$/i', $val) && defined($val)) {
            return constant($val);
        }

        return $val;
    }
}
