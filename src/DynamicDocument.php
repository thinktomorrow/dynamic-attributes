<?php

declare(strict_types=1);

namespace Thinktomorrow\DynamicAttributes;

final class DynamicDocument
{
    private array $values;

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function has(string $key): bool
    {
        return ($this->get($key, '__NOTFOUND__') !== '__NOTFOUND__');
    }

    /**
     * @param string $key
     * @param null|mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return data_get($this->values, $key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        data_set($this->values, $key, $value);
    }

    public function remove(string $key): void
    {
        $parts = explode('.', $key);
        $last = array_pop($parts);

        $values = &$this->values;

        foreach ($parts as $part) {
            $values = &$values[$part];
        }

        unset($values[$last]);
    }

    public function all(): array
    {
        return $this->values;
    }

    public function merge(array $values): self
    {
        return new static(array_merge($this->values, $values));
    }

    public function toJson(): string
    {
        return json_encode($this->all());
    }
}
