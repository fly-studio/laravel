<?php

namespace Addons\Server\Contracts;

use BadMethodCallException;
use InvalidArgumentException;
use Addons\Server\Routing\Router;
use Addons\Func\Console\ConsoleLog;
use Addons\Server\Structs\ConnectPool;
use Addons\Server\Listeners\PidListener;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractSender;
use Addons\Server\Contracts\AbstractListener;
use Addons\Server\Contracts\AbstractObserver;
use Addons\Server\Structs\Config\ServerConfig;

abstract class AbstractServer {

	protected $config;
	protected $pool;
	protected $server;
	protected $observer;
	protected $router;
	protected $capture;
	protected $observerListeners = [];

	public function __construct(ServerConfig $config)
	{
		$this->config = $config;

		$this->validateConfig($config);

		ConsoleLog::$daemon = $config->daemon;
		ConsoleLog::$debug = config('app.debug');

		$this->pool = new ConnectPool();
		$this->router = $this->createRouter();
		$this->server = $this->createServer($config);
		$this->initServer($this->server, $config);
		$this->observer = $this->createObserver();

		foreach($this->observerListeners as $method)
			$this->server->on($method, [$this->observer, 'on'.$method]);

		// load system's listeners
		foreach($this->getSystemListeners() as $listener)
			$this->listening($listener);
	}

	protected function createRouter()
	{
		return new Router(app('events'), app());
	}

	abstract protected function validateConfig(ServerConfig $config);
	abstract protected function createServer(ServerConfig $config): \swoole_server;
	abstract protected function initServer(\swoole_server $server, ServerConfig $config);
	abstract protected function createObserver(): AbstractObserver;
	abstract protected function makeSender(ConnectBinder $binder, ...$args): AbstractSender;
	abstract protected function getSystemListeners(): array;

	/**
	 * 添加监听器，比如是AbstractListener的子类
	 *
	 * 注意：如果覆盖同名监听函数，那只会触发这次设置的监听函数，之前设置的同名监听函数会被删除
	 * 这里的覆盖并不是指类的覆盖，而是指覆盖类中的回调函数名(onXXX之类)
	 *
	 * @param AbstractListener $listenerClass
	 * @param bool $overwrite 是否覆盖同名监听函数
	 * @return $this
	 */
	public function listening($listenerClass, bool $overwrite = false)
	{
		$this->observer->addClassListener($listenerClass, $overwrite);
		return $this;
	}

	/**
	 * 设置pid文件路径，server会在启动、关闭时维护这个pid文件
	 * 注意：在server其中，此文件禁止写入
	 *
	 * @param string $pidPath
	 */
	public function setPidPath(string $pidPath)
	{
		$pidListener = new PidListener($this);
		$pidListener->pidPath($pidPath);

		$this->listening($pidListener);
	}

	/**
	 * 设置解码协议
	 *
	 * @param  AbstractProtocol $capture 解码协议实例
	 * @return $this
	 */
	public function capture(AbstractProtocol $capture)
	{
		$this->capture = $capture;
		$capture->bootIfNotBooted($this);
		return $this;
	}

	/**
	 * 读取一个路由文件
	 *
	 * @param  string $file_path 文件绝对路径
	 * @param  string $namespace Controller的默认namespace
	 * @return $this
	 */
	public function loadRoutes(string $file_path, string $namespace = 'App\\Tcp\\Controllers')
	{
		$this->router->load($file_path, $namespace);
		return $this;
	}

	/**
	 * 获取swoole的server
	 * @return [type] [description]
	 */
	public function nativeServer(): \swoole_server
	{
		return $this->server;
	}

	/**
	 * 获取路由表
	 * @return Router [description]
	 */
	public function router(): Router
	{
		return $this->router;
	}

	/**
	 * 获取服务器配置
	 * @return ServerConfig [description]
	 */
	public function config(): ServerConfig
	{
		return $this->config;
	}

	public function pool(): ConnectPool
	{
		return $this->pool;
	}

	public function observer(): AbstractObserver
	{
		return $this->observer;
	}

	public function protocol(): AbstractProtocol
	{
		return $this->capture;
	}

	/**
	 * 核心request、response函数，
	 * 接受数据之后，进入本函数
	 *
	 * @param  ConnectBinder $binder
	 * @param  [type]        $args
	 */
	public function handle(ConnectBinder $binder, ...$args)
	{
		if (empty($this->capture))
			return;

		try {

			$request = $this->capture->decode($binder, ...$args);
			if (empty($request))
				return;

			$request->with($binder);
			$result = $this->router->dispatchToRoute($request);

			$response = $this->capture->encode($request, $result, ...$args);
			if (empty($response))
				return;

			$response->with($binder, $this->makeSender($binder, ...$args));
			$response->bootIfNotBooted();
			$response->prepare($request);
			$response->send();

		} catch (\Exception $e) {
			$this->capture->failed($binder, $e);
		} catch (\Throwable $e) {
			$this->capture->failed($binder, $e);
		}
	}

	/**
	 * 服务器开启监听
	 *
	 */
	public function run()
	{
		$this->server->start();
	}

	/**
	 * 服务器关闭
	 *
	 */
	public function shutdown()
	{
		$this->server->shutdown();
	}

	/**
	 * Get Native server's property
	 * @param  [type] $property
	 * @return [type]
	 */
	public function __get($property)
	{
		if (property_exists($this->server, $property))
			return $this->server->$property;

		return null;
	}

	/**
	 * Get Native server's method
	 * @param  string $method
	 * @param  [type] $args
	 * @return [type]
	 */
	public function __call($method, $args)
	{
		if (method_exists($this->server, $method))
			return $this->server->$method(...$args);

		throw new BadMethodCallException(sprintf(
			'Method %s::%s does not exist.', static::class, $method
		));
	}

}
