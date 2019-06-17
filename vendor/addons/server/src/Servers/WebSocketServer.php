<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\HttpServer;
use Addons\Server\Senders\HttpSender;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Senders\WebSocketSender;
use Addons\Server\Contracts\AbstractSender;
use Addons\Server\Contracts\AbstractProtocol;
use Addons\Server\Contracts\AbstractObserver;
use Addons\Server\Observers\WebSocketObserver;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Listeners\WebSocketListener;

/**
 * 可以同时接收http/ws，因为ws在没握手之前本身就是http server
 * 1、capture 可以设置ws协议、http协议，这两个协议的处理是独立的
 * 2、如果capture传递了第二个参数，则需要调用loadWebRoutes，也就设置http的路由
 * 3、如果在http协议中，如果你需要类似php-fpm（nginx）一样访问当前的网站所有页面，可以使用NativeWebSocketServer，它将比php-fpm的效率更高
 */
class WebSocketServer extends HttpServer {

	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Request', 'Close', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop', 'Connect', 'Message', 'Open', 'HandShake'];

	protected $webRouter;
	protected $webCapture;

	public function __construct(ServerConfig $config)
	{
		parent::__construct($config);

		$this->webRouter = $this->createRouter();
	}

	protected function createServer(ServerConfig $config): \swoole_server
	{
		return new \swoole_websocket_server($config->host()->host(), $config->host()->port(), SWOOLE_PROCESS, SWOOLE_SOCK_TCP | (!empty($config->ssl_cert_file()) && !empty($config->ssl_key_file()) ? SWOOLE_SSL : 0));
	}

	protected function makeSender(ConnectBinder $binder, ...$args): AbstractSender
	{
		return $binder->getBindIf('ws-sender', function() use($binder, $args) {
			return new WebSocketSender($binder, ...$args);
		});
	}

	protected function makeWebSender(ConnectBinder $binder, ...$args): AbstractSender
	{
		//One tunnel has multi-http-stream
		//http1.1中一个fd会有多个http流，所以需要独立的sender类
		return new HttpSender($binder, ...$args);
	}

	/**
	 * 设置Ws/Http解码协议
	 *
	 * @param  AbstractProtocol $capture 解码协议实例
	 * @return $this
	 */
	public function capture(AbstractProtocol $capture, AbstractProtocol $webCapture = null)
	{
		if (!is_null($webCapture))
		{
			$this->webCapture = $webCapture;
			$webCapture->bootIfNotBooted($this);
		}

		return parent::capture($capture);
	}

	/**
	 * 读取一个Web路由文件
	 *
	 * @param  string $file_path 文件绝对路径
	 * @param  string $namespace Controller的默认namespace
	 * @return $this
	 */
	public function loadWebRoutes(string $file_path, string $namespace = 'App\\Tcp\\Controllers')
	{
		$this->webRouter->load($file_path, $namespace);
		return $this;
	}

	protected function createObserver(): AbstractObserver
	{
		return new WebSocketObserver($this);
	}

	protected function getSystemListeners(): array
	{
		return [WebSocketListener::class];
	}

	/**
	 * 核心request、response函数，
	 * 接受数据之后，进入本函数
	 *
	 * @param  ConnectBinder $binder
	 * @param  [type]        $args
	 */
	public function webHandle(ConnectBinder $binder, ...$args)
	{
		if (empty($this->webCapture))
			return;

		try {

			$request = $this->webCapture->decode($binder, ...$args);
			if (empty($request))
				return;

			$request->with($binder);
			$result = $this->webRouter->dispatchToRoute($request);

			$response = $this->webCapture->encode($request, $result, ...$args);
			if (empty($response))
				return;

			$response->with($binder, $this->makeWebSender($binder, ...$args));
			$response->bootIfNotBooted();
			$response->prepare($request);
			$response->send();

		} catch (\Exception $e) {
			$this->webCapture->failed($binder, $e);
		} catch (\Throwable $e) {
			$this->webCapture->failed($binder, $e);
		}
	}

}
