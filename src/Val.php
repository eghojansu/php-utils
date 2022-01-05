<?php

namespace Ekok\Utils;

class Val
{
    public static function normKey(string $str): string
    {
        return str_replace('\\.', '.', $str);
    }

    public static function dotKeys($key, array &$parts = null, &$nkey = null): bool
    {
        list($nkey, $parts) = is_string($key) && false !== strpos($key, '.') ? array(
            static::normKey($key),
            array_map(static::class . '::normKey', preg_split('/(?<!\\\\)\./', $key)),
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
            if ('' === $part) {
                continue;
            }

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
