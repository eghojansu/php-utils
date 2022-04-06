<?php

declare(strict_types=1);

namespace Ekok\Utils;

class Call
{
    public static function check(string $expr, int &$pos = null): bool
    {
        return 0 < ($pos = false === ($at = strpos($expr, '@')) ? strpos($expr, ':') : $at);
    }

    public static function standarize(string|object $class, string $method, bool $static = false): string|array
    {
        if (is_string($class)) {
            return $class . ($static ? '::' : '@') . $method;
        }

        return array($class, $method);
    }

    public static function chain($data, callable ...$cbs)
    {
        $context = new Context($data);

        foreach ($cbs as $cb) {
            $result = $cb(...$context->getArguments());

            if ($result !== $context) {
                $context->setData(array($result));
            }
        }

        return $context->getValue();
    }
}
