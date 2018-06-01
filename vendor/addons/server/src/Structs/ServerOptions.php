<?php

namespace Addons\Server\Structs;

use Addons\Server\Servers\Server;
use Addons\Server\Console\ConsoleLog;
use Addons\Func\Contracts\MutatorTrait;

class ServerOptions {

	use MutatorTrait;

	protected $server;
	protected $unique = null;
	protected $swoole_event = null;
	protected $server_socket = null;
	protected $socket_type = null;
	protected $client_ip = null;
	protected $client_port = null;
	protected $server_port = null;
	protected $file_descriptor = null;
	protected $reactor_id = null;

	public function __construct(Server $server)
	{
		$this->server = $server;
	}

	public function logger($type, $message, $operation = 'recv')
	{
		if ($type == 'hex')
			return ConsoleLog::$type($message, ['bytes_per_line' => 32]);
		else if ($operation == 'recv')
			$message = sprintf('[RID: %x] %s from [%s:%s] of %d'/*, getmypid()*/, $this->reactor_id, $message, $this->client_ip, $this->client_port, $this->file_descriptor);
		else if ($operation == 'send')
			$message = sprintf('[RID: %x] %s to [%s:%s] of %d'/*, getmypid()*/, $this->reactor_id, $message, $this->client_ip, $this->client_port, $this->file_descriptor);

		ConsoleLog::$type($message);
	}

	public function server()
	{
		return $this->server;
	}

	public function toArray()
	{
		return [
			'unique' => $this->unique,
			'server_socket' => $this->server_socket,
			'socket_type' => $this->socket_type,
			'client_ip' => $this->client_ip,
			'client_port' => $this->client_port,
			'server_port' => $this->server_port,
			'file_descriptor' => $this->file_descriptor,
			'reactor_id' => $this->reactor_id,
		];
	}

	public function __sleep()
	{
		return [
			'unique',
			'server_socket',
			'socket_type',
			'client_ip',
			'client_port',
			'server_port',
			'file_descriptor',
			'reactor_id',
		];
	}

}
