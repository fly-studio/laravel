<?php

namespace Addons\Server\Structs\Config;

use Addons\Func\Contracts\MutatorTrait;

class Host {

	use MutatorTrait;

	public $host;
	public $port;
	public $protocol;

	public function __construct(string $host, int $port, int $protocol = SWOOLE_SOCK_TCP)
	{
		$this->host = $host;
		$this->port = $port;
		$this->protocol = $protocol;
	}

	public static function build(string $host, int $port, int $protocol = SWOOLE_SOCK_TCP)
	{
		return new static($host, $port, $protocol);
	}
}
