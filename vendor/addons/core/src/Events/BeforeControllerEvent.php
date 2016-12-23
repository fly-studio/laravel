<?php

namespace Addons\Core\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Addons\Core\Contracts\Events\ControllerEvent as ControllerEventContract;

class BeforeControllerEvent  implements ControllerEventContract
{
    use ControllerEventTrait;
    use InteractsWithSockets, SerializesModels;

    public $controllerObject;
    public $methodName;
    public $parameters;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($controllerObject, $methodName, $parameters)
    {
        $this->controllerObject = $controllerObject;
        $this->methodName = $methodName;
        $this->parameters = $parameters;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('controller.before');
    }
}
