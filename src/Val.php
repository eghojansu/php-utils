<?php

declare(strict_types=1);

namespace Ekok\Utils;

class Val
{
    public static function isEmpty($val, bool $expected = true): bool
    {
        return $expected === (null === $val || '' === $val || (is_countable($val) && 0 === count($val)));
    }

    public static function isTrue($value): bool
    {
        return static::compare($value, true);
    }

    public static function isFalse($value): bool
    {
        return static::compare($value, false);
    }

    public static function compare($value, $with, bool $strict = true): bool
    {
        $compare = $value instanceof \Closure ? $value() : $value;

        return $strict ? $compare === $with : $compare == $with;
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

    public static function dotKeys($key, array &$parts = null, &$nkey = null): bool
    {
        $norm = static fn (string $str) => str_replace('\\.', '.', trim($str, ". \t\n\r\0\x0B"));

        list($nkey, $parts) = is_string($key) && false !== strpos($key, '.') ? array(
            $norm($key),
            array_map($norm, preg_split('/(?<!\\\\)\./', $key, -1, PREG_SPLIT_NO_EMPTY)),
        ) : array($key, array($key));

        return isset($parts[1]);
    }

    public static function &ref($key, array &$ref, bool $add = false, bool &$exists = null, array &$parts = null)
    {
        if ($add) {
            $var = &$ref;
        } else {
            $var = $ref;
        }

        $dot = static::dotKeys($key, $parts, $nkey);

        if (!$dot || ($found = Arr::exists($var, $nkey))) {
            $exists = $found ?? Arr::exists($var, $nkey);
            $var = &$var[$nkey];

            return $var;
        }

        foreach ($parts as $part) {
            if (null === $var || is_scalar($var)) {
                $var = array();
            }

            if (is_array($var) || $var instanceof \ArrayAccess) {
                $exists = Arr::exists($var, $part);
                $var = &$var[$part];
            } elseif (is_object($var) && is_callable($get = array($var, 'get' . $part))) {
                $exists = true;
                $var = $get();
            } elseif (is_object($var)) {
                $exists = isset($var->$part);
                $var = &$var->$part;
            } else {
                $exists = false;
                $var = null;

                break;
            }
        }

        return $var;
    }

    public static function unref($key, array &$ref): void
    {
        if (!static::dotKeys($key, $parts, $nkey)) {
            unset($ref[$nkey]);

            return;
        }

        $leaf = array_pop($parts);
        $root = implode('.', array_map(fn($part) => str_replace('.', '\\.', $part), $parts));
        $var = &static::ref($root, $ref, true);

        if (is_array($var) || $var instanceof \ArrayAccess) {
            unset($var[$leaf]);
        } elseif (is_object($var) && is_callable($remove = array($var, 'remove' . $leaf))) {
            $remove();
        } elseif (is_object($var) && isset($var->$leaf)) {
            unset($var->$leaf);
        }
    }
}
