<?php

namespace Addons\Server\Contracts;

use Addons\Server\Contracts\Listeners\IListener;

interface IFire extends IListener {
	public function handle(?string $raw);
}
