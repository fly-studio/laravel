<?php

namespace Addons\Server\Servers;

use Addons\Server\Structs\ConnectBinder;


/**
 * 這個HttpServer類似一個nginx服務，啟動後，原Laravel實現的網站不需要任何修改，便可以在瀏覽器中訪問
 *
 * 注意：
 * 1. 如果在延時任務、異步任務中，通過app(Request::class)、request(), app(Route::class)、route()來獲取Request、Route，得到的結果將會是未知的。
 * 一定要將這些對象傳遞到異步函數中
 * 2. 如果使用dd() dump() exit() die() 将导致swoole退出，或输出到swoole的控制台
 */
class NativeHttpServer extends HttpServer {

	public function loadRoutes(string $file_path, string $namespace = 'App\\Http\\Controllers')
	{
		throw new \RunTimeException('Native Http server does not need to define routes.');
	}

	/**
	 * 重寫核心類
	 * 核心request、response函数，
	 * 接受数据之后，进入本函数
	 *
	 * @param  ConnectBinder $binder
	 * @param  [type]        $args
	 */
	public function handle(ConnectBinder $binder, ...$args)
	{
		try {

			$request = $this->capture->decode($binder, ...$args);

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

			$response = $this->capture->encode($request, $laravelResponse, ...$args);

			if (empty($response))
				return;

			$response->with($binder, $this->makeSender($binder, ...$args));
			$response->bootIfNotBooted();
			$response->prepare($request);
			$response->send();

			// 結束中間件
			$kernel->terminate($laravelRequest, $laravelResponse);

		} catch(\Exception $e) {
			$this->capture->failed($binder, $e);
		} catch (\Throwable $e) {
			$this->capture->failed($binder, $e);
		}
	}

}
