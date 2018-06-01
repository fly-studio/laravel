<?php
namespace Plugins\Utils\App\Proxies;

use Addons\Server\Console\ConsoleLog;
use Plugins\Utils\App\Structs\ServerOptions;
use Plugins\Utils\App\Contracts\AbstractProxy;

class SyncProxyPass extends AbstractProxy {

	public function __construct(ServerOptions $options, $proxy, array $callbacks = [])
	{
		parent::__construct($options, $proxy, $callbacks);

		$this->client = new \swoole_client($options->socket_type() | SWOOLE_KEEP);

		$this->client->set([
			'package_max_length' => 1024 * 1024 * 2,
		]);

		$this->logger('info', 'Started.');
		$this->retries = 0;
		$this->connect();
	}

	public function close($closeFromServer = true)
	{
		$this->closeFromServer = $closeFromServer;
		if ($this->connected && $this->client->isConnected())
		{
			$this->client->close();
			$this->connected = false;
			$this->onClose($this->client);
		}
	}

	public function connect()
	{
		if ($this->connected) return;
		if (!$this->client->connect($this->proxy['host'], $this->proxy['port'], $this->proxy['timeout'])) //eg: DNS timeout 2s
			return $this->onError($this->client);
		else
			return $this->onConnect($this->client);
	}

	protected function sendByList()
	{
		if (!$this->connected) return false;
		foreach($this->data as $data)
		{
			if (is_null($data) || $data === '')
				continue;
			$this->retries = 0;
			while($this->retries < $this->proxy['retries'] && $this->client->send($data) === false)
				++$this->retries;

			$this->onSend($this->client, $data);
		}
		$this->data = [];
	}

	public function recv()
	{
		$recv = null;
		$this->retries = 0;
		while($this->retries < $this->proxy['retries'])
		{
			try {
				$recv = $this->client->recv();
				$this->onReceive($this->client, $recv);
				break;
			} catch (\Exception $e) {
				$this->logger('error', $e->getMessage());
				++$this->retries;
			}
		}
		return $this;
	}

	public function logger($type, $message, $operation = 'send')
	{
		if ($type != 'hex')
			$message = sprintf('[%s] %s', 'SYNC-Proxy', $message);
		parent::logger($type, $message, $operation);
	}

}
