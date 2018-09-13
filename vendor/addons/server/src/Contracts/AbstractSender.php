<?php

namespace Addons\Server\Contracts;

use Addons\Server\Structs\ServerOptions;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Contracts\AbstractServer;

abstract class AbstractSender {

	protected $binder;

	public function __construct(ConnectBinder $binder)
	{
		$this->binder = $binder;
	}

	public function options(): ServerOptions
	{
		return $this->binder->options();
	}

	public function binder(): ConnectBinder
	{
		return $this->binder;
	}

	/**
	 * 标准Send一条数据
	 * TCP 最大不能超过buffer_output_size, UDP不能超过65507
	 *
	 * @param  string $raw 数据
	 * @return int
	 */
	abstract public function send(string $raw): int;
	/**
	 * 按照分割的方式Send一条数据
	 * 比如HTTP中，会发送Chunked头，并按照 lengt\r\ndata 的方式发送数据
	 *
	 * @param  string $raw
	 * @return int
	 */
	abstract public function chunk(string $raw): int;

	/**
	 * 发送一个文件
	 *
	 * @param  string      $path
	 * @param  int|integer $offset
	 * @param  int|null    $length
	 * @return int
	 */
	abstract public function file(string $path, int $offset = 0, int $length = null): int;

	/**
	 * 发送结束包，如果没有需要结尾的包，可以实现空函数
	 * 比如Http的Chunked，以及Websocket
	 *
	 * @return int
	 */
	abstract public function end(): int;

	//abstract public function broadcast(callable $rawCallback, ...$fd_list);

}
