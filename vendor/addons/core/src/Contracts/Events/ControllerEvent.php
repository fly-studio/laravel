<?php
namespace Addons\Core\Contracts\Events;

interface ControllerEvent {
	
	public function getClass();
    public function getClassName();
    public function getMethod();
    public function getParameters();


}