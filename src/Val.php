<?php

namespace Ekok\Utils;

class Val
{
    public static function &ref($key, array &$ref, bool $add = false, bool &$exists = null, array &$parts = null)
    {
        if ($add) {
            $var = &$ref;
        } else {
            $var = $ref;
        }

        if (
            ($exists = isset($var[$key]) || array_key_exists($key, $var))
            || !is_string($key)
            || false === strpos($key, '.')
        ) {
            $parts = array($key);
            $var = &$var[$key];

            return $var;
        }

        $parts = explode('.', $key);

        foreach ($parts as $part) {
            if ('' === $part) {
                continue;
            }

            if (null === $var || is_scalar($var)) {
                $var = array();
            }

            if (($arr = is_array($var)) || $var instanceof \ArrayAccess) {
                $exists = isset($var[$part]) || ($arr && array_key_exists($part, $var));
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
        if (false === ($pos = strrpos($key, '.'))) {
            unset($ref[$key]);

            return;
        }

        $root = substr($key, 0, $pos);
        $leaf = substr($key, $pos + 1);
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
