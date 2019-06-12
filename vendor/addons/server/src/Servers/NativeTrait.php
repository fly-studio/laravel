<?php

namespace Addons\Server\Servers;

use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Protocols\Http\NativeProtocol;

trait NativeTrait {

	protected $webCapture;

	protected function initServer(\swoole_server $server, ServerConfig $config)
	{
		if (empty($this->webCapture))
			$this->webCapture = new NativeProtocol();

		parent::initServer($server, $config);
	}

	/**
	 * 重寫核心類
	 * 核心request、response函数，
	 * 接受数据之后，进入本函数
	 *
	 * @param  ConnectBinder $binder
	 * @param  [type]        $args
	 */
	public function webHandle(ConnectBinder $binder, ...$args)
	{

		try {

			$request = $this->webCapture->decode($binder, ...$args);

			/**
			 * 調用Laravel的Request -> Middleware -> Route -> Middleware -> Controller -> Middleware -> Response生命週期來實現頁面的渲染
			 *
			 * 目前已知問題
			 * app(Request::class)、request(), app(Route::class)、route()會被後面建立的Http會話覆蓋
			 * 如果在延時任務中，比如swoole的timer中或者投遞到onTask，獲取request、route將會不準確，解決辦法是：將當前$request、$route傳入這些函數
			 * session()使用情況未知
			 *
			 * @var [type]
			 */
			$kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);

			$laravelResponse = $kernel->handle($laravelRequest = $request->getLaravelRequest());

			$response = $this->webCapture->encode($request, $laravelResponse, ...$args);

			if (empty($response))
				return;

			$response->with($binder, $this->makeWebSender($binder, ...$args));
			$response->bootIfNotBooted();
			$response->prepare($request);
			$response->send();

			// 結束中間件
			$kernel->terminate($laravelRequest, $laravelResponse);

		} catch(\Exception $e) {
			$this->webCapture->failed($binder, $e);
		} catch (\Throwable $e) {
			$this->webCapture->failed($binder, $e);
		}
	}
}
