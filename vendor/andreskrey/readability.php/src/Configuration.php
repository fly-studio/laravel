<?php

namespace andreskrey\Readability;

class Configuration
{
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param array $config
     */
    public function merge(array $config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);
    }

    /**
     * @param array $config
     */
    public function replace(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setOption($key, $value)
    {
        $this->config[$key] = $value;
    }

    /**
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return mixed|null
     */
    public function getOption($key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        if (!isset($this->config[$key])) {
            return $default;
        }

        return $this->config[$key];
    }
}
