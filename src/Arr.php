<?php

namespace Ekok\Utils;

class Arr
{
    public static function exists(\ArrayAccess|array $items, $key): bool
    {
        return isset($items[$key]) || (is_array($items) && array_key_exists($key, $items));
    }

    public static function each(iterable $items, callable $cb, bool $skipNulls = false): array
    {
        $payload = new Payload($items);

        foreach ($payload->items as $key => $value) {
            $result = $cb($payload->update($value, $key));
            $update = $result instanceof Payload ? $payload->value : $result;

            if ($skipNulls && null === $update) {
                continue;
            }

            $payload->commit($update);
        }

        return $payload->result;
    }

    public static function filter(iterable $items, callable $cb): array
    {
        $payload = new Payload($items);

        foreach ($payload->items as $key => $value) {
            if ($cb($payload->update($value, $key))) {
                $payload->commit($payload->value);
            }
        }

        return $payload->result;
    }

    public static function walk(iterable $items, callable $cb): void
    {
        $payload = new Payload($items);

        foreach ($payload->items as $key => $value) {
            $cb($payload->update($value, $key));
        }
    }

    public static function some(iterable $items, callable $cb, &$last = null): bool
    {
        $payload = new Payload($items);
        $last = null;

        foreach ($payload->items as $key => $value) {
            if ($cb($payload->update($value, $key))) {
                $last = $payload->value;

                return true;
            }
        }

        return false;
    }

    public static function every(iterable $items, callable $cb): bool
    {
        $payload = new Payload($items);

        foreach ($payload->items as $key => $value) {
            if (!$cb($payload->update($value, $key))) {
                return false;
            }
        }

        return true;
    }

    public static function includes(iterable $items, $value, bool $strict = false): bool
    {
        $include = fn($value) => is_array($items) ? in_array($value, $items, $strict) : static::some($items, fn(Payload $item) => $strict ? $item->value === $value : $item->value == $value);

        return is_iterable($value) ? static::every($value, fn(Payload $item) => $include($item->value)) : $include($value);
    }

    public static function first(iterable $items, callable $cb)
    {
        $payload = new Payload($items);

        foreach ($payload->items as $key => $value) {
            $result = $cb($payload->update($value, $key));
            $update = $result instanceof Payload ? $payload->value : $result;

            if (null !== $update) {
                return $update;
            }
        }

        return null;
    }

    public static function reduce(iterable $items, callable $cb, $carry = null)
    {
        $payload = new Payload($items);

        foreach ($payload->items as $key => $value) {
            $carry = $cb($carry, $payload->update($value, $key));
        }

        return $carry;
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

    public static function without(array|null $items, string|int ...$keys): array
    {
        return array_diff_key($items ?? array(), array_fill_keys($keys, null));
    }

    public static function ensure(array|string $items, string $symbols = null): array
    {
        return is_string($items) ? Str::split($items, $symbols) : $items;
    }

    public static function fill(array|string $items, $value = true, string $symbols = null): array
    {
        return array_fill_keys(static::ensure($items, $symbols), $value);
    }
}
