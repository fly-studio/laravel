<?php

namespace Addons\Server\Contracts;

use Addons\Func\Structs\StreamChunks;
use Addons\Server\Structs\ServerOptions;

abstract class ProxyListener {

	private $closeFromClient = false;
	protected $proxy = null;
	protected $request = null;
	protected $response = null;
	protected $requestBuff = null; // Buff is not complete data
	protected $responseBuff = null;
	protected $onProxyConnect = null;
	protected $onProxyClose = null;
	protected $onProxyError = null;
	protected $onProxySend = null;
	protected $onProxyReceive = null;

	public function __construct(ServerOptions $options)
	{
		$this->requestBuff = new StreamChunks;
		$this->responseBuff = new StreamChunks;
		$this->options = $options;
	}

	public function proxy()
	{
		return $this->proxy;
	}

	public function startProxy($proxyParameters)
	{
		if (is_null($this->proxy))
		{
			$callbacks = [
				'connect' => [$this, '_onProxyConnect'],
				'close' => [$this, '_onProxyClose'],
				'error' => [$this, '_onProxyError'],
				'send' => [$this, '_onProxySend'],
				'receive' => [$this, '_onProxyReceive'],
			];
			$this->proxy = new ProxyPass($this->options, $proxyParameters, $callbacks);
		}

		return $this;
	}

	public function stopProxy()
	{
		$this->closeFromClient = true;
		if (!is_null($this->proxy))
			$this->proxy->close();
		$this->proxy = null;
	}

	public function _onProxyConnect($client)
	{
		if (is_callable($this->onProxyConnect))
			return call_user_func($this->onProxyConnect, $client);
	}

	public function _onProxyClose($client)
	{
		if (is_callable($this->onProxyClose))
			return call_user_func($this->onProxyClose, $client);

		if ($this->options->socket_type() == SWOOLE_SOCK_TCP && !$this->closeFromClient)
			$this->options->server()->close($this->options->file_descriptor());
		// UDP has not this event
	}

	public function _onProxyError($client)
	{
		if (is_callable($this->onProxyError))
			return call_user_func($this->onProxyError, $client);

		if ($this->options->socket_type() == SWOOLE_SOCK_TCP)
			$this->options->server()->close($this->options->file_descriptor());
	}

	public function _onProxyReceive($client, $data)
	{
		if (is_callable($this->onProxyReceive))
			return call_user_func($this->onProxyReceive, $client, $data);
	}

	public function _onProxySend($client, $data)
	{
		if (is_callable($this->onProxySend))
			return call_user_func($this->onProxySend, $client, $data);
	}

	public function __destruct()
	{
		$this->stopProxy();
	}
}
