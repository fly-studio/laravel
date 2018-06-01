<?php

namespace Addons\Server\Structs\Config;

use Addons\Func\Contracts\MutatorTrait;

class Listen {

	use MutatorTrait;

	public $host;
	public $port;
	public $protocol;

	public function __construct(int $port, string $host = '127.0.0.1', int $protocol = SWOOLE_SOCK_TCP)
	{
		$this->host = $host;
		$this->port = $port;
		$this->protocol = $protocol;
	}

	public static function build(int $port, string $host = '127.0.0.1', int $protocol = SWOOLE_SOCK_TCP)
	{
		return new static($port, $host, $protocol);
	}
}
