<?php

namespace Obelaw\Ium\Core\Engine;

use Obelaw\Ium\Core\Contracts\IumConfigEnum;

final class ObelawConfigManager
{
    private static ?self $instance = null;

    private array $configs = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get(IumConfigEnum|string $enum, mixed $default = null): mixed
    {
        $module = null;
        $key = null;

        if ($enum instanceof IumConfigEnum) {
            $module = $enum->module();
            $key = $enum->key();
        } elseif (is_string($enum)) {
            $parts = explode('.', $enum, 2);
            $module = $parts[0] ?? null;
            $key = $parts[1] ?? $enum;
        }

        if ($module === null || $key === null) {
            return $default;
        }

        return $this->configs[$module][$key] ?? $default;
    }

    public function set(IumConfigEnum|string $enum, mixed $value): self
    {
        if ($enum instanceof IumConfigEnum) {
            $module = $enum->module();
            $key = $enum->key();
        } else {
            $parts = explode('.', $enum, 2);
            $module = $parts[0] ?? null;
            $key = $parts[1] ?? $enum;
        }

        if ($module !== null && $key !== null) {
            if (!isset($this->configs[$module])) {
                $this->configs[$module] = [];
            }

            $this->configs[$module][$key] = $value;
        }

        return $this;
    }

    public function merge(string $module, array $configs): self
    {
        if (!isset($this->configs[$module])) {
            $this->configs[$module] = [];
        }

        $this->configs[$module] = array_replace_recursive(
            $this->configs[$module],
            $this->flattenArray($configs, $module)
        );

        return $this;
    }

    public function hasModule(string $module): bool
    {
        return isset($this->configs[$module]);
    }

    public function getModule(string $module): array
    {
        return $this->configs[$module] ?? [];
    }

    public function all(): array
    {
        return $this->configs;
    }

    public function reset(): self
    {
        $this->configs = [];

        return $this;
    }

    public function getEnum(IumConfigEnum $enum, mixed $default = null): mixed
    {
        return $this->configs[$enum->module()][$enum->key()] ?? $default;
    }

    public function setEnum(IumConfigEnum $enum, mixed $value): self
    {
        $module = $enum->module();
        $key = $enum->key();

        if (!isset($this->configs[$module])) {
            $this->configs[$module] = [];
        }

        $this->configs[$module][$key] = $value;

        return $this;
    }

    public function forget(IumConfigEnum|string $enum): self
    {
        if ($enum instanceof IumConfigEnum) {
            $module = $enum->module();
            $key = $enum->key();
        } else {
            $parts = explode('.', $enum, 2);
            $module = $parts[0] ?? null;
            $key = $parts[1] ?? $enum;
        }

        if ($module !== null && $key !== null) {
            unset($this->configs[$module][$key]);
        }

        return $this;
    }

    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }
}
