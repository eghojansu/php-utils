<?php

namespace Ekok\Utils;

class Arr
{
    public static function each(iterable $items, callable $cb, bool $skipNulls = false): array
    {
        $payload = new Payload($items);

        foreach ($payload->items as $key => $value) {
            $result = $cb($payload->update($value, $key));
            $update = null === $value || $result instanceof Payload ? $payload->value : $value;

            if ($skipNulls && null === $update) {
                continue;
            }

            $payload->result[$payload->key] = $update;
        }

        return $payload->result;
    }

    public static function walk(iterable $items, callable $cb): void
    {
        $payload = new Payload($items);

        foreach ($payload->items as $key => $value) {
            $cb($payload->with($value, $key));
        }
    }

    public static function some(iterable $items, callable $cb, &$last = null): bool
    {
        $payload = new Payload($items);
        $last = null;

        foreach ($payload->items as $key => $value) {
            if ($cb($payload->with($value, $key))) {
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
            if (!$cb($payload->with($value, $key))) {
                return false;
            }
        }

        return true;
    }

    public static function reduce(iterable $items, callable $cb, $carry = null)
    {
        $payload = new Payload($items);

        foreach ($payload->items as $key => $value) {
            $carry = $callback($carry, $cb($payload->with($value, $key)));
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

    public static function ensure(array|string $items, string $sep = ',;|'): array
    {
        return is_string($arr) ? Str::split($arr, $sep) : $arr;
    }
}
