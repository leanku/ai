<?php
declare(strict_types=1);

namespace Leanku\Ai\Support;

class Config
{
    public function __construct(private array $config = [])
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }
        return $value;
    }

    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->config;
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return false;
            }
            $value = $value[$key];
        }
        return true;
    }

    public function all(): array
    {
        return $this->config;
    }
}