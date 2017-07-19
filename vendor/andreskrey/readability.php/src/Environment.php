<?php

namespace andreskrey\Readability;

final class Environment
{
    /**
     * @var Configuration
     */
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = new Configuration($config);
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return Environment
     */
    public static function createDefaultEnvironment(array $config = [])
    {
        $environment = new static($config);

        return $environment;
    }
}
