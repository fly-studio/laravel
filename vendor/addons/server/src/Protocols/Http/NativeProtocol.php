<?php

namespace Addons\Server\Protocols\Http;

use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Protocols\Http\Request;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Contracts\AbstractProtocol;

use Addons\Server\Protocols\Http\Responses\Response;
use Addons\Server\Protocols\Http\Responses\FileResponse;
use Addons\Server\Protocols\Http\Responses\ChunkResponse;
use Addons\Server\Protocols\Http\Responses\RedirectResponse;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;

/**
 * 搭配NativeHttpServer使用
 */
class NativeProtocol extends AbstractProtocol {

	public function decode(ConnectBinder $binder, ...$args) : ?AbstractRequest
	{
		return new Request(...$args);
	}

	public function encode(AbstractRequest $request, $laravelResponse, ...$args): ?AbstractResponse
	{
		if ($laravelResponse instanceof SymfonyResponse)
		{
			$response = null;

			// 文件下載
			if ($laravelResponse instanceof SymfonyBinaryFileResponse)
			{
				$response = new FileResponse($laravelResponse->getFile(), get_property($laravelResponse, 'offset'), get_property($laravelResponse, 'maxlen'));
				$response->deleteFileAfterSend(get_property($laravelResponse, 'deleteFileAfterSend'));
			}
			// 跳轉URL
			else if ($laravelResponse instanceof SymfonyRedirectResponse)
			{
				$response = new RedirectResponse($laravelResponse->getTargetUrl(), $laravelResponse->getStatusCode());
			}
			// 多次輸出
			else if ($laravelResponse instanceof SymfonyStreamedResponse)
			{
				$response = new ChunkResponse(get_property($laravelResponse, 'callback'));
			}
			// 其它
			else
			{
				$response = new Response($laravelResponse->getContent());
			}

			// 設置 HTTP 狀態
			$response->status($laravelResponse->getStatusCode());

			// 設置 headers
			foreach ($laravelResponse->headers->allPreserveCaseWithoutCookies() as $name => $values) {
				foreach ($values as $value) {
					$response->header($name, $value);
				}
			}

			// 設置 cookies
			foreach ($laravelResponse->headers->getCookies() as $cookie) {
				$response->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
			}

			return $response;
		}

		return new Response(!empty($laravelResponse) ? strval($laravelResponse) : null);
	}

}
