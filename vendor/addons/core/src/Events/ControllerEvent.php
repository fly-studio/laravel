<?php

namespace Addons\Core\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Addons\Core\Contracts\Events\ControllerEvent as ControllerEventContract;

class ControllerEvent implements ControllerEventContract
{
    use InteractsWithSockets, SerializesModels;

    public $controllerObject;
    public $methodName;
    public $parameters;
    public $response;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($controllerObject, $methodName, $parameters, $response)
    {
        $this->controllerObject = $controllerObject;
        $this->methodName = $methodName;
        $this->parameters = $parameters;
        $this->response = $response;
    }

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

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('controller');
    }
}
