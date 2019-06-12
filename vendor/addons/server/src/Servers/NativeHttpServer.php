<?php

namespace Addons\Server\Servers;

use Addons\Server\Servers\NativeTrait;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractSender;
use Addons\Server\Contracts\AbstractProtocol;

/**
 * 這個HttpServer類似一個nginx服務，啟動後，原Laravel實現的網站不需要任何修改，便可以在瀏覽器中訪問
 *
 * 注意：
 * 1. 如果在延時任務、異步任務中，通過app(Request::class)、request(), app(Route::class)、route()來獲取Request、Route，得到的結果將會是未知的。
 * 一定要將這些對象傳遞到異步函數中
 * 2. 如果使用dd() dump() exit() die() 将导致swoole退出，或输出到swoole的控制台
 */
class NativeHttpServer extends HttpServer {

	use NativeTrait;

	public function loadRoutes(string $file_path, string $namespace = 'App\\Http\\Controllers')
	{
		throw new \RunTimeException('Native Http server does not need to define routes.');
	}

	/**
	 * 设置解码协议
	 *
	 * @param  AbstractProtocol $capture 解码协议实例
	 * @return $this
	 */
	public function capture(AbstractProtocol $capture)
	{
		$this->webCapture = $capture;
		return parent::capture($capture);
	}

	protected function makeWebSender(ConnectBinder $binder, ...$args): AbstractSender
	{
		return $this->makeSender($binder, ...$args);
	}

	public function handle(ConnectBinder $binder, ...$args)
	{
		$this->webHandle($binder, ...$args);
	}

}
