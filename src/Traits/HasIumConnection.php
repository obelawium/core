<?php

namespace Obelaw\Ium\Core\Traits;

use Obelaw\Ium\Core\Contracts\IumConfigEnum;

trait HasIumConnection
{
    protected static ?string $configConnectionEnum = null;
    protected static ?string $configPrefixEnum = null;

    public static function useConnectionFrom(IumConfigEnum $enum): void
    {
        static::$configConnectionEnum = get_class($enum);
    }

    public static function usePrefixFrom(IumConfigEnum $enum): void
    {
        static::$configPrefixEnum = get_class($enum);
    }

    public function getConnectionName(): string
    {
        $connectionEnum = static::$configConnectionEnum;

        if ($connectionEnum !== null) {
            $cases = $connectionEnum::cases();
            
            foreach ($cases as $case) {
                if (str_starts_with($case->key(), 'database.connection')) {
                    $connection = ium()->config()->get($case);
                    
                    if ($connection !== null) {
                        return $connection;
                    }
                }
            }
        } else {
            $module = $this->detectModuleFromNamespace();

            if ($module !== null) {
                $connectionKey = "{$module}.database.connection";
                $connection = ium()->config()->get($connectionKey);

                if ($connection !== null) {
                    return $connection;
                }
            }
        }

        return parent::getConnectionName();
    }

    private function detectModuleFromNamespace(): ?string
    {
        $namespace = static::class;

        $segments = explode('\\', $namespace);

        $iumIndex = array_search('Ium', $segments, true);

        if ($iumIndex !== false && isset($segments[$iumIndex + 1])) {
            return strtolower($segments[$iumIndex + 1]);
        }

        return null;
    }

    public static function bootHasIumConnection(): void
    {
        static::resolved(function (self $model) {
            $prefixEnum = static::$configPrefixEnum;

            if ($prefixEnum !== null) {
                $cases = $prefixEnum::cases();
                
                foreach ($cases as $case) {
                    if (str_starts_with($case->key(), 'database.prefix')) {
                        $prefix = ium()->config()->get($case);

                        if ($prefix !== null) {
                            $model->setTable($prefix . $model->getTable());
                            
                            return;
                        }
                    }
                }
            } else {
                $module = $model->detectModuleFromNamespace();

                if ($module !== null) {
                    $prefixKey = "{$module}.database.prefix";
                    $prefix = ium()->config()->get($prefixKey);

                    if ($prefix !== null) {
                        $model->setTable($prefix . $model->getTable());
                    }
                }
            }
        });
    }
}
