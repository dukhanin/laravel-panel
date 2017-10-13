<?php
namespace Dukhanin\Panel\Traits;

trait HasConfig
{
    protected $config;

    protected function initConfig()
    {
        $this->config = config('panel');
    }

    public function config($key = null, $default = null)
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }

        return array_get($this->config, $key, $default);
    }

    public function setConfig($key, $value = null)
    {
        if (is_null($this->config)) {
            $this->initConfig();
        }

        if(func_num_args() == 1) {
            $this->config = (array) $key + $this->config;
        } else {
            array_set($this->config, $key, $value);
        }
    }
}