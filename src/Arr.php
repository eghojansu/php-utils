<?php

declare(strict_types=1);

namespace Ekok\Utils;

class Arr
{
    public static function formatTrace(\Throwable|array $trace = null, int $flags = null): array
    {
        return array_map(
            static fn (array $frame) => (
                rtrim(($frame['file'] ?? '') . ':' . ($frame['line'] ?? ''), ':') . ' ' .
                ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '')
            ),
            $trace instanceof \Throwable ? $trace->getTrace() : (
                $trace ?? array_slice(debug_backtrace($flags ?? DEBUG_BACKTRACE_IGNORE_ARGS), 1)
            ),
        );
    }

    public static function indexed(array $items): bool
    {
        return $items && is_numeric(implode('', array_keys($items)));
    }

    public static function exists(\ArrayAccess|array $items, $key): bool
    {
        return isset($items[$key]) || (is_array($items) && array_key_exists($key, $items));
    }

    public static function filter(iterable $items, callable $cb): array
    {
        $filtered = array();

        foreach ($items as $key => $value) {
            if ($cb($value, $key, $items, $filtered)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    public static function each(iterable $items, callable $cb, bool $ignoreNull = false, bool $indexed = false): array
    {
        $result = array();

        foreach ($items as $key => $value) {
            $update = $cb($value, $key, $items, $result);

            if ($ignoreNull && null === $update) {
                continue;
            }

            if ($indexed) {
                $result[] = $update;
            } else {
                $result[$key] = $update;
            }
        }

        return $result;
    }

    public static function some(iterable $items, callable $cb, array &$last = null): bool
    {
        $last = null;

        foreach ($items as $key => $value) {
            if ($cb($value, $key, $items)) {
                $last = array($key, $value);

                return true;
            }
        }

        return false;
    }

    public static function every(iterable $items, callable $cb): bool
    {
        foreach ($items as $key => $value) {
            if (!$cb($value, $key, $items)) {
                return false;
            }
        }

        return !!$items;
    }

    public static function first(iterable $items, callable $cb)
    {
        foreach ($items as $key => $value) {
            $result = $cb($value, $key, $items);

            if (null !== $result) {
                return $result;
            }
        }

        return null;
    }

    public static function reduce(iterable $items, callable $cb, $carry = null)
    {
        $result = $carry;

        foreach ($items as $key => $value) {
            $result = $cb($result, $value, $key, $items);
        }

        return $result;
    }

    public static function includes(iterable $items, $value, bool $strict = false): bool
    {
        return static::every(
            is_iterable($value) ? $value : (array) $value,
            fn($value) => (
                is_array($items) ?
                    in_array($value, $items, $strict) :
                    static::some($items, fn($item) => $strict ? $item === $value : $item == $value)
            ),
        );
    }

    public static function walk(iterable $items, callable $cb): void
    {
        foreach ($items as $key => $value) {
            $cb($value, $key, $items);
        }
    }

    public static function quoteKeys(array $items, string|array $quote = null): array
    {
        $open = $quote[0] ?? '"';
        $close = $quote[1] ?? $open;

        return static::reduce(
            $items,
            static fn(array $arr, $value, $key) => $arr + array($open . $key . $close => $value),
            array(),
        );
    }

    public static function merge(iterable|null ...$arrays): array
    {
        $result = array();

        foreach ($arrays as $row) {
            foreach ($row ?? array() as $key => $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function ignore(array|null $items, string|int ...$keys): array
    {
        return array_diff_key($items ?? array(), array_fill_keys($keys, null));
    }

    public static function ensure(array|string|null $items, string $symbols = null): array
    {
        return is_string($items) ? Str::split($items, $symbols) : $items ?? array();
    }

    public static function fill(array|string $items, $value = true, string $symbols = null): array
    {
        return array_fill_keys(static::ensure($items, $symbols), $value);
    }
}
