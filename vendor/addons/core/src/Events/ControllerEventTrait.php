<?php

namespace Addons\Core\Events;

use ReflectionClass;
use ReflectionProperty;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;

trait ControllerEventTrait
{

	public function getClass()
    {
        return $this->controllerObject;
    }

    public function getClassName()
    {
        return get_class($this->controllerObject);
    }

    public function getMethod()
    {
        return $this->methodName;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

}