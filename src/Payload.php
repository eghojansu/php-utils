<?php

namespace Ekok\Utils;

class Payload
{
    public function __construct(
        public iterable $items,
        public array $result = array(),
        public $key = null,
        public $value = null,
    ) {}

    public function valType(string $type = null): string|bool
    {
        return $type ? $type === gettype($this->value) : gettype($this->value);
    }

    public function keyType(string $type = null): string|bool
    {
        return $type ? $type === gettype($this->key) : gettype($this->key);
    }

    public function commit($value): static
    {
        if (null === $this->key || '' === $this->key) {
            $this->result[] = $value;
        } else {
            $this->result[$this->key] = $value;
        }

        return $this;
    }

    public function update($value, $key = null): static
    {
        $this->value = $value;
        $this->key = $key;

        return $this;
    }

    public function value($value): static
    {
        $this->value = $value;

        return $this;
    }

    public function key($key): static
    {
        $this->key = $key;

        return $this;
    }
}
