<?php

namespace Addons\Server\Servers;

use RuntimeException;
use BadMethodCallException;
use Addons\Server\Console\ConsoleLog;
use Addons\Server\Servers\Observer\Observer;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Server {

	protected $server;
	protected $observer;
	protected $config;
	protected $protocolListener = null;
	protected $services = [];
	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Connect', 'Receive', 'Packet', 'Close', 'BufferFull', 'BufferEmpty', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop'];

	public function __construct(ServerConfig $config)
	{
		$this->config = $config;

		$this->validateConfig($config);

		ConsoleLog::$daemon = $config->daemon;
		ConsoleLog::$debug = config('app.debug');

		$this->createServer();
		$this->initServer();
		$this->observe();
	}

	protected function createServer()
	{
		$this->server = new \swoole_server($this->config->listen()->host(), $this->config->listen()->port(), $this->config->daemon() ? SWOOLE_PROCESS : SWOOLE_BASE, $this->config->listen()->protocol());
	}

	protected function validateConfig(ServerConfig $config)
	{
		if (!is_numeric($config->listen()->port()))
			throw new RuntimeException('Server port required.');
		if (empty($config->listen()->protocol()))
			throw new RuntimeException('Server protocol required');
	}

	protected function initServer()
	{
		$config = $this->config;
		$this->server->set([
			'task_worker_num' => $config->task_worker_num(),
			'worker_num' => $config->worker_num(),
			'daemonize' => $config->daemon(),
			'backlog' => $config->backlog(),
			'user' => $config->user(),
			'group' => empty($config->group()) ? $config->user() : $config->group(),
			'heartbeat_check_interval' => $config->heartbeat_check_interval(),
			'heartbeat_idle_time' => $config->heartbeat_idle_time(),
		]);

		foreach($config->sub_listens() as $listen)
			$this->server->listen($listen->host, $listen->port, $listen->protocol);
	}

	protected function observe()
	{
		$this->observer = new Observer($this);

		foreach($this->observerListeners as $method)
			$this->server->on($method, [$this->observer, 'on'.$method]);
	}

	public function use(AbstractProtocolListener $protocolListener)
	{
		$this->protocolListener = $protocolListener;
		return $this;
	}

	public function addService(AbstractService $servie)
	{
		$this->servies[] = $servie;
		return $this;
	}

	public function getProtocolListener()
	{
		return $this->protocolListener;
	}

	public function getNativeServer()
	{
		return $this->server;
	}

	public function getConfig()
	{
		return $this->config;
	}

	public function run()
	{
		if (empty($this->protocolListener))
			throw new RuntimeException('set a Protocol Listener first: $server->use(new SomeProtocolListener).');

		$this->observer->setProtocolListener($this->protocolListener);
		$this->server->start();
	}

	public function shutdown()
	{
		$this->server->shutdown();
	}

	/**
	 * Get Native server's method
	 * @param  string $method [description]
	 * @param  [type] $args   [description]
	 * @return [type]         [description]
	 */
	public function __call(string $method, $args)
	{
		if (method_exists($this->server, $method))
			return $this->server->$method(...$args);

		throw new BadMethodCallException(sprintf(
			'Method %s::%s does not exist.', static::class, $method
		));
	}

	/**
	 * Get Native server's property
	 * @param  [type] $property [description]
	 * @return [type]           [description]
	 */
	public function __get($property)
	{
		if (property_exists($this->server, $property))
			return $this->server->$property;

		return null;
	}
}
