<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\Server;
use Addons\Server\Senders\HttpSender;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Observers\HttpObserver;
use Addons\Server\Protocols\Http\Protocol;
use Addons\Server\Listeners\HttpListener;
use Addons\Server\Contracts\AbstractSender;
use Addons\Server\Contracts\AbstractObserver;
use Addons\Server\Structs\Config\ServerConfig;

use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * Http Server 需要指定類似Tcp的路由，並且Request、Response的用法也和Tcp的類似，所以需要手动的东西较多
 *
 * 如果你需要类似php-fpm（nginx）一样访问当前的网站的所有页面，可以使用NativeHttpServer，它将比php-fpm的效率更高
 *
 * 注意：如果使用dd() dump() exit() die() 将导致swoole退出，或输出到swoole的控制台
 */
class HttpServer extends Server {

	protected $observerListeners = ['Start', 'Shutdown', 'WorkerStart', 'WorkerStop', 'Request', 'Close', 'Task', 'Finish', 'PipeMessage', 'WorkerError', 'ManagerStart', 'ManagerStop', 'Connect'];

	protected function redirectDumper()
	{
		$_SERVER['VAR_DUMPER_FORMAT'] = 'html';
		VarDumper::setHandler(function($value) {
			$data = (new VarCloner)->cloneVar($value);

			(new HtmlDumper(function($line, $depth, $indentPad){

			}))->dump($data);
		});
	}

	protected function createServer(ServerConfig $config): \swoole_server
	{
		return new \swoole_http_server($config->host()->host(), $config->host()->port(), SWOOLE_PROCESS, SWOOLE_SOCK_TCP | (!empty($config->ssl_cert_file()) && !empty($config->ssl_key_file()) ? SWOOLE_SSL : 0));
	}

	protected function initServer(\swoole_server $server, ServerConfig $config)
	{
		//extra http config
		if (!empty($config->ssl_cert_file()) && !empty($config->ssl_key_file()))
		{
			$server->set([
				'ssl_cert_file' => $config->ssl_cert_file(),
				'ssl_key_file' => $config->ssl_key_file(),
				'ssl_ciphers' => $config->ssl_ciphers(),
				'ssl_method' => $config->ssl_method(),
			]);
		}

		$server->set([
			'http_compression' => true,
			'upload_tmp_dir' => $config->upload_tmp_dir(),
			'http_parse_post' => $config->http_parse_post(),
			'package_max_length' => $config->package_max_length(),
			'document_root' => $config->document_root(),
			'enable_static_handler' => $config->enable_static_handler(),
			'static_handler_locations' => $config->static_handler_locations(),
		]);


		parent::initServer($server, $config);

		//$this->redirectDumper();

		if (empty($this->capture))
			$this->capture = new Protocol();

	}

	protected function makeSender(ConnectBinder $binder, ...$args): AbstractSender
	{
		//One tunnel has multi-http-stream
		//http1.1中一个fd会有多个http流，所以需要独立的sender类
		return new HttpSender($binder, ...$args);
	}

	protected function createObserver(): AbstractObserver
	{
		return new HttpObserver($this);
	}

	protected function getSystemListeners(): array
	{
		return [HttpListener::class];
	}
}
