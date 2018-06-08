<?php

namespace Addons\Server\Servers;

use RuntimeException;
use BadMethodCallException;
use Addons\Server\Routing\Router;
use Addons\Func\Console\ConsoleLog;
use Addons\Server\Servers\Observer\Observer;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Server {

	protected $server;
	protected $observer;
	protected $config;
	protected $router;
	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Connect', 'Receive', 'Packet', 'Close', 'BufferFull', 'BufferEmpty', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop'];

	public function __construct(ServerConfig $config)
	{
		$this->config = $config;
		$this->router = new Router(app('events'), app());

		$this->validateConfig($config);

		ConsoleLog::$daemon = $config->daemon;
		ConsoleLog::$debug = config('app.debug');

		$this->createServer();
		$this->initServer();
		$this->observe();
	}

	protected function createServer()
	{
		$this->server = new \swoole_server($this->config->host()->host(), $this->config->host()->port(), $this->config->daemhost() ? SWOOLE_PROCESS : SWOOLE_BASE, $this->config->host()->protocol());
	}

	protected function validateConfig(ServerConfig $config)
	{
		if (!is_numeric($config->host()->port()))
			throw new RuntimeException('Server port required.');
		if (empty($config->host()->protocol()))
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

	public function capture(AbstractProtocolListener $protocolListener)
	{
		$this->observer->setListener($protocolListener);
		return $this;
	}

	/**
	 * 注册 特征值 和 service类名
	 * 在protocolListener中的analyzing分析其匹配情况
	 *
	 * @param string          $eigenvalue 特征值
	 * @param stromg $servie  Service class     服务
	 */
	public function registerRoute(string $eigenvalue, $action)
	{
		$this->router->register($eigenvalue, $action);
		return $this;
	}

	public function loadRoutes(string $file_path, string $namespace)
	{
		$this->router->load($file_path, $namespace);
	}

	public function nativeServer()
	{
		return $this->server;
	}

	public function router()
	{
		return $this->router;
	}

	public function config()
	{
		return $this->config;
	}

	public function protocolListener()
	{
		return $this->observer->getListener();
	}

	public function run()
	{
		if (empty($this->protocolListener()))
			throw new RuntimeException('Capture a Protocol Listener before run: $server->capture(new SomeProtocolListener).');

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
