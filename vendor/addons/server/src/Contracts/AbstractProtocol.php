<?php

namespace Addons\Server\Contracts;

use Addons\Server\Response\RawResponse;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;

abstract class AbstractProtocol {

	/**
	 * 自定义该方法，分析数据之后返回不同的Request
	 *
	 * @param  ServerOptions $options 客户端/服务端的连接参数
	 * @param  array         $args    原始内容、或者是swoole_request, swoole_response
	 * @return AbstractRequest
	 */
	abstract public function decode(ServerOptions $options, ...$args): ?AbstractRequest;

	/**
	 * 自定义该方法，将Controller执行之后的结果进行封装和分析，返回正确的Response
	 *
	 * @param  AbstractRequest $request  上面decode函数得到的Request
	 * @param  [mixed]         $response null,字符串,或者response
	 * @param  [array]         $args     原始内容、或者是swoole_request, swoole_response
	 * @return [AbstractResponse]
	 */
	public function encode(AbstractRequest $request, $response, ...$args): ?AbstractResponse
	{
		if ($response instanceof AbstractResponse)
			$response = $response;
		else if (empty($response) && !is_numeric($response))
			return null;
		else
			$response = new RawResponse(@strval($response));

		return $response;
	}


	public function failed(ServerOptions $options, \Exception $e)
	{
		$options->logger('error', $e->getMessage());
		$options->logger('debug', $e->getTraceAsString());
	}
}