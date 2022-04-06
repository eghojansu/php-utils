<?php

declare(strict_types=1);

namespace Ekok\Utils;

class Context
{
    /** @var array */
    private $data;
    private $value;

    public function __construct($data = null)
    {
        $this->setData($data);
    }

    public function getArguments(): array
    {
        return array_merge($this->data, array($this));
    }

    public function getData(): array
    {
        return $this->data ?? array();
    }

    public function setData($data): static
    {
        $this->data = (array) $data;

        return $this;
    }

    public function pop()
    {
        return array_pop($this->valArr()->value);
    }

    public function push(...$values): static
    {
        array_push($this->valArr()->value, ...$values);

        return $this;
    }

    public function shift()
    {
        return array_shift($this->valArr()->value);
    }

    public function unshift(...$values): static
    {
        array_unshift($this->valArr()->value, ...$values);

        return $this;
    }

    public function getValue()
    {
        return $this->value ?? $this->data[0] ?? null;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    private function valArr(): static
    {
        if (!is_array($this->value)) {
            $this->value = (array) $this->value;
        }

        return $this;
    }
}
