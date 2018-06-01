<?php

namespace Addons\Server\Proxies;

use Addons\Server\Console\ConsoleLog;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractProxy;

class ProxyPass extends AbstractProxy {

	public function __construct(ServerOptions $options, $proxy, array $callbacks = [])
	{
		parent::__construct($options, $proxy, $callbacks);

		$this->client = new \swoole_client($options->socket_type(), SWOOLE_SOCK_ASYNC);

		$this->client->set([
			'package_max_length' => 1024 * 1024 * 2,
		]);
		foreach(['Connect', 'Receive', 'Error', 'Close'] as $on)
			$this->client->on($on, [$this, 'on'.$on]);

		$this->logger('info', 'Started.');
		$this->retries = 0;
		$this->connect();
	}

	public function close($closeFromServer = true)
	{
		$this->closeFromServer = $closeFromServer;
		if ($this->connected && $this->client->isConnected())
			$this->client->close();
		$this->connected = false;
	}

	public function connect()
	{
		if ($this->connected) return;
		$this->client->connect($this->proxy['host'], $this->proxy['port'], $this->proxy['timeout']);
	}

	protected function sendByList()
	{
		if (!$this->connected) return false;
		foreach($this->data as $data)
		{
			if (is_null($data) || $data === '')
				continue;
			$this->client->send($data);
			$this->onSend($this->client, $data);
		}
		$this->data = [];
	}

	public function recv()
	{
		//do nothing
		return $this;
	}

	public function logger($type, $message, $operation = 'send')
	{
		if ($type != 'hex')
			$message = sprintf('[%s] %s', 'Proxy', $message);
		parent::logger($type, $message, $operation);
	}
}
