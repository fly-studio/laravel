<?php

namespace Addons\Server\Servers;

use RuntimeException;
use Addons\Server\Senders\Sender;
use Addons\Server\Senders\UdpSender;
use Addons\Server\Senders\TcpSender;
use Addons\Server\Observers\Observer;
use Addons\Server\Listeners\TcpListener;
use Addons\Server\Listeners\UdpListener;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractServer;
use Addons\Server\Contracts\AbstractSender;
use Addons\Server\Contracts\AbstractObserver;
use Addons\Server\Structs\Config\ServerConfig;

class Server extends AbstractServer {

	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Connect', 'Receive', 'Packet', 'Close', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop'];

	protected function createServer(ServerConfig $config): \swoole_server
	{
		return new \swoole_server($config->host()->host(), $config->host()->port(), SWOOLE_PROCESS, $config->host()->protocol());
	}

	protected function validateConfig(ServerConfig $config)
	{
		if (!is_numeric($config->host()->port()))
			throw new RuntimeException('Server port required.');

		if (empty($config->host()->protocol()))
			throw new RuntimeException('Server protocol required');
	}

	protected function initServer(\swoole_server $server, ServerConfig $config)
	{
		foreach($config->sub_hosts() as $host)
			$server->listen($host->host, $host->port, $host->protocol);

		$server->set([
			'task_worker_num' => $config->task_worker_num(),
			'worker_num' => $config->worker_num(),
			'daemonize' => $config->daemon(),
			'backlog' => $config->backlog(),
			'user' => $config->user(),
			'group' => empty($config->group()) ? $config->user() : $config->group(),
			'heartbeat_check_interval' => $config->heartbeat_check_interval(),
			'heartbeat_idle_time' => $config->heartbeat_idle_time(),
		]);
	}

	protected function makeSender(ConnectBinder $binder, ...$args): AbstractSender
	{
		$sender = null;
		$options = $binder->options();

		switch ($options->socket_type()) {
			case SWOOLE_SOCK_UDP:
			case SWOOLE_SOCK_UDP6:
				$sender = new UdpSender($binder, ...$args);
				break;
			case SWOOLE_SOCK_TCP:
			case SWOOLE_SOCK_TCP6:
				$sender = $binder->getBindIf('sender', function() use($binder, $args) {
					return new TcpSender($binder, ...$args);
				});
				break;
		}
		return $sender;
	}

	protected function createObserver(): AbstractObserver
	{
		return new Observer($this);
	}

	protected function getProtocolMask(ServerConfig $config)
	{
		$result = 0;

		$protocols = [$config->host()->protocol() &~ SWOOLE_SSL];
		foreach($config->sub_hosts() as $host)
			$protocols[] = $host->protocol() &~ SWOOLE_SSL;

		if (!empty(array_intersect([SWOOLE_SOCK_TCP, SWOOLE_SOCK_TCP6], $protocols)))
			$result |= SWOOLE_SOCK_TCP;

		if (!empty(array_intersect([SWOOLE_SOCK_UDP, SWOOLE_SOCK_UDP6], $protocols)))
			$result |= SWOOLE_SOCK_UDP;

		return $result;
	}

	protected function getSystemListeners(): array
	{
		$result = [];

		$mask = $this->getProtocolMask($this->config);

		if ($mask & SWOOLE_SOCK_TCP)
			$result[] = TcpListener::class;

		if ($mask & SWOOLE_SOCK_UDP)
			$result[] = UdpListener::class;

		return $result;
	}
}
