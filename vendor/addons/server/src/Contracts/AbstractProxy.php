<?php

namespace Addons\Server\Contracts;

use Addons\Server\Console\ConsoleLog;
use Addons\Server\Structs\ServerOptions;

abstract class AbstractProxy {

	protected $client;
	protected $connected = false;
	protected $options = null;
	protected $proxy = null;
	protected $retries = 0;
	protected $data = [];
	protected $callbacks = [];

	public function __construct(ServerOptions $options, $proxy, array $callbacks = [])
	{
		$this->options = $options;
		$this->proxy = $proxy;
		$this->callbacks = $callbacks;
	}

	public function __destruct()
	{
		$this->close();
	}

	abstract public function close();
	abstract public function connect();
	abstract protected function sendByList();
	abstract public function recv();


	public function send($data)
	{
		foreach (array_wrap($data) as $d)
			$this->data[] = $d;

		$this->sendByList();

		return $this;
	}

	public function onConnect(/*\swoole_client*/ $client)
	{
		$this->connected = true;
		$this->logger('info', 'connected to Remote.');
		$this->sendByList();

		if (!empty($this->callbacks['connect']) && is_callable($this->callbacks['connect'])) return call_user_func($this->callbacks['connect'], $client);
	}

	public function onSend(/*\swoole_client*/$client, string $data)
	{
		$this->logger('info', 'send data: ');
		$this->logger('hex', $data);

		if (!empty($this->callbacks['send']) && is_callable($this->callbacks['send'])) return call_user_func($this->callbacks['send'], $client, $data);
	}

	public function onReceive(/*\swoole_client*/ $client, string $data)
	{
		$this->logger('info', 'receive data: ', 'recv');
		$this->logger('hex', $data);

		if (!empty($this->callbacks['receive']) && is_callable($this->callbacks['receive'])) return call_user_func($this->callbacks['receive'], $client, $data);
	}

	public function onError(/*\swoole_client*/ $client)
	{
		$this->connected = false;
		$this->logger('error', 'Cannot connect to Remote.');

		if (++$this->retries < $this->proxy['retries'])
			$this->connect();
		else
			if (!empty($this->callbacks['error']) && is_callable($this->callbacks['error'])) return call_user_func($this->callbacks['error'], $client);
	}

	public function onClose(/*\swoole_client*/ $client)
	{
		$this->connected = false;
		$this->logger('info', 'Close the Proxy.');

		if (!empty($this->callbacks['close']) && is_callable($this->callbacks['close'])) return call_user_func($this->callbacks['close'], $client);
	}

	public function logger($type, $message, $operation = 'send')
	{
		if ($type == 'hex')
			return ConsoleLog::$type($message, ['bytes_per_line' => 32]);
		else if ($operation == 'recv')
			$message = sprintf('%s from [%s:%s] of %x', $message, $this->proxy['host'], $this->proxy['port'], $this->options->unique());
		else if ($operation == 'send')
			$message = sprintf('%s to [%s:%s] of %x', $message, $this->proxy['host'], $this->proxy['port'], $this->options->unique());

		ConsoleLog::$type($message);
	}

}
