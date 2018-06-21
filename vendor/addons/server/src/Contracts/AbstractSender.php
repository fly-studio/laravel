<?php

namespace Addons\Server\Contracts;

use Addons\Server\Contracts\AbstractServer;

abstract class AbstractSender {

	/**
	 * 标准Send一条数据
	 * TCP 最大不能超过buffer_output_size, UDP不能超过65507
	 *
	 * @param  string $raw 数据
	 * @return [type]
	 */
	abstract public function send(string $raw): int;
	/**
	 * 按照分割的方式Send一条数据
	 * 比如HTTP中，会发送Chunked头，并按照 lengt\r\ndata 的方式发送数据
	 *
	 * @param  string $raw [description]
	 * @return [type]      [description]
	 */
	abstract public function chunk(string $raw): int;

	/**
	 * 发送一个文件
	 *
	 * @param  string      $path   [description]
	 * @param  int|integer $offset [description]
	 * @param  int|null    $length [description]
	 * @return [type]              [description]
	 */
	abstract public function file(string $path, int $offset = 0, int $length = null): int;


	//abstract public function broadcast(callable $rawCallback, ...$fd_list);

}
