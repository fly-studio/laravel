<?php

namespace Addons\Server\Contracts;

use Closure;
use RuntimeException;
use Addons\Func\Contracts\BootTrait;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;

abstract class AbstractResponse {

	use BootTrait;

	protected $nextAction;

	protected $content = null;

	public function __construct($content = null)
	{
		$this->content = $content;
	}

	public static function build(...$args)
	{
		return new static(...$args);
	}

	public function options(ServerOptions $options = null)
	{
		if (is_null($options)) return $this->options;

		$this->options = $options;
		return $this;
	}

	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	public function getContent()
	{
		return $this->content;
	}

	public function server()
	{
		$this->throwNullServerOptions();

		return $this->options->server();
	}

	public function nextAction(callable $callback = null)
	{
		if (is_null($callback)) return $this->nextAction;

		$this->nextAction = $callback;
		return $this;
	}

	protected function sendUDP(string $_data)
	{
		$this->options->logger('info', 'UDP reply: ', 'send');
		$this->options->logger('hex', $_data);
		list(, $fd) = unpack('L', pack('N', ip2long($this->options->client_ip())));
		$reactor_id = ($this->options->server_socket() << 16) + $this->options->client_port();

		return $this->server()->send($fd, $_data, $reactor_id);
	}

	protected function sendTCP(string $_data)
	{
		$this->options->logger('info', 'TCP reply: ', 'send');
		$this->options->logger('hex', $_data);
		return $this->server()->send($this->options->file_descriptor(), $_data);
	}

	public function prepare(AbstractRequest $request)
	{
		return $this;
	}

	public function sendContent()
	{
		$data = $this->content;

		if (empty($data) && !is_numeric($data))
			return;

		switch($this->options->socket_type())
		{
			case SWOOLE_SOCK_UDP:
				$this->sendUDP($data);
				break;
			case SWOOLE_SOCK_TCP:
				$this->sendTCP($data);
				break;
		}
	}

	public function send()
	{
		$this->throwNullServerOptions();

		$this->sendContent();
	}

	protected function throwNullServerOptions()
	{
		if (empty($this->options))
			throw new RuntimeException('Set a valid ServerOptions before this booted.');

	}

}
