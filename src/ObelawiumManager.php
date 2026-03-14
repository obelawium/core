<?php

namespace Obelaw\Ium\Core;

use Illuminate\Support\Traits\Macroable;

class ObelawiumManager
{
    use Macroable;

    protected array $configs = [];

    public function setConfigs(array $configs)
    {
        $this->configs = array_merge($this->configs, $configs);
    }

    public function getConfigs()
    {
        return $this->configs;
    }
}
