<?php

namespace Addons\Server\Contracts;

use Addons\Server\Servers\Server;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Response\TextResponse;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;

abstract class AbstractFire {

	protected $server;

	public function __construct(Server $server)
	{
		$this->server = $server;
	}

	public function server()
	{
		return $this->server;
	}

	/**
	 * 可自定义该方法，分析数据之后返回不同的Service
	 *
	 * @param  ServerOptions $options 客户端/服务端的连接参数
	 * @param  ?string  $raw     原始内容
	 * @return AbstractService
	 */
	abstract public function analyzing(ServerOptions $options, ?string $raw): AbstractRequest;

	public function handle(AbstractRequest $request): ?AbstractResponse
	{
		$response = $this->server()->router()->dispatchToRoute($request);

		if ($response instanceof AbstractResponse)
			$response = $response;
		else if (empty($response) && !is_numeric($response))
			return null;
		else
			$response = new TextResponse(@strval($response));

		$response->options($request->options());
		$response->boot();
		$response->prepare($request);

		return $response;
	}


	public function failed(ServerOptions $options, \Exception $e)
	{
		$options->logger('error', $e->getMessage());
		$options->logger('debug', $e->getTraceAsString());
	}
}
