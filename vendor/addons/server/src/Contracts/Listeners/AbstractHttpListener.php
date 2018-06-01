<?php

namespace Addons\Server\Contracts\Listeners;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\Requests\AbstractRequest;

class AbstractHttpListener extends AbstractProtocolListener {

	protected $logPrefix = '[HTTP Server]';

	/**
	 * 可自定义该方法，分析数据之后返回不同的Request
	 *
	 * @param  Options $options [description]
	 * @param  [type]  $raw     [description]
	 * @return [type]           [description]
	 */
	protected function analyze(ServerOptions $options, $raw, \swoole_http_request $request, \swoole_http_response $response) : AbstractRequest
	{
		return new RawRequest($options, $raw);
	}

	public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
	{
		$options = $this->makeServerOptions($request->fd, $request->reactor_id);

		$this->analyze($options, $reques->getData(), $request, $response);
	}

}
