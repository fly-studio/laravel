<?php

namespace Addons\Server\Client;

use Co, Exception;
use RuntimeException;
use Swoole\Coroutine\Client;
use Swoole\Coroutine\Channel;
use Addons\Server\Structs\Pool;
use Addons\Func\Console\ConsoleLog;
use Addons\Server\Exceptions\ConnectionException;

// 一个Swoole\Client的实现的Tcp连接池
// 这个类只能运行在go(function(){ .... });中
// 注意所有的实现都是协程的，发送和接收不会阻塞其它任务，根据测试异步发送效率要比同步高很多。
class TcpPool extends Pool {

	protected $host;
	protected $port;
	protected $serverConfig;

	/**
	 *
	 * @example
	 * 比如设置一个自动分割 | 2bytes | LENGTH 4bytes | DATA | 的 配置
	 * $serverConfig = [
	 * 	'open_length_check' => true,
	 * 	'package_length_type' => 'N',
	 * 	'package_length_offset' => 2,
	 * 	'package_body_offset' => 6,
	 * ]
	 *
	 * @param string      $host         [description]
	 * @param int         $port         [description]
	 * @param array       $serverConfig [description]
	 * @param int|integer $poolCount    [description]
	 */
	public function __construct(string $host, int $port, array $serverConfig = [], int $poolCount = 10)
	{
		$this->host = $host;
		$this->port = $port;
		$this->serverConfig = $serverConfig;

		parent::__construct($poolCount);
	}

	protected function doNewConnect()
	{
		$client = new Client(SWOOLE_SOCK_TCP);
		!empty($this->serverConfig) && $client->set($this->serverConfig);

		$client->connect($this->host, $this->port);

		// https://wiki.swoole.com/wiki/page/p-client/close.html
		// 当一个swoole_client连接被close后不要再次发起connect。正确的做法是销毁当前的swoole_client，重新创建一个swoole_client并发起新的连接。

		return $client;
	}

	protected function isConnected($client)
	{
		return $client->isConnected() && $client->errCode == 0;
	}

	protected function doClose($client, bool $force)
	{
		$client->close($force);
	}

	/**
	 * 同步发送并接收数据
	 * 同步发送必须要等待上一个发送（或返回）之后才能进行下一个发送
	 *
	 * 如果池内连接意外断开，会一直重试寻找下一个可用连接(协程,不会阻塞)
	 *
	 * 注意：使用本函数最好设置$serverConfig的open_length_check等参数，让swoole自动分割每个包
	 * 其次，这个只能recv一个包，如果需要多个返回的，只能用callAsync然后自己实现recv
	 *
	 * @example [URI] [<description>]
	 *
	 * @param  string $data         RAW 数据
	 * @param  bool   $needResponse 是否需要回执
	 * @return string               回执
	 */
	public function call(string $data, bool $needResponse = true)
	{
		return $this->callSync($data, $needResponse ? $this->makeSimpleRecvCall() : null);
	}

	/**
	 * 异步发送数据并接收回执
	 *
	 * 如果发送、接收失败，需要自己实现重试功能
	 *
	 * 如果recvCallable内部发生异常，会被捕获并写入日志，然后抛出，然后请自行捕获(见下例)
	 *
	 * 注意: 同时发送多条时，$recvCallable执行的顺序是乱序的，因为连接池以及协程的缘故，其次server端返回的也可能不是按照顺序来，也就是说send顺序是：1 2 3，回复顺序可能是1 3 2
	 * 如果上下文有关联，执行的时候最好使用将上下文引入到匿名函数中(见下例)
	 *
	 * @example
	 * 接收多条
	 * try {
	 * $pool->callAsync($data, function($client) use ($xxx) { // $xxx 表示上下文的变量
	 * 	$recv = $client->recv();
	 * 	$recv .= $client->recv(); //接收多条
	 *
	 *  $xxx->setRecv($recv); // 可操作与之关联的对象
	 * });
	 *
	 * catch(Exception $e) {
	 *	// $e 捕获到错误
	 * }
	 *
	 * @param  string        $data          RAW DATA
	 * @param  callable|null $recvCallable  需要回调的函数，只有一个参数 $client，如果客户端连接异常，需要您在这个函数内返回false，不然只有等到下一次send时才能发现连接问题
	 * @return int                          协程ID
	 */
	public function callAsync(string $data, callable $recvCallable = null)
	{
		return go(function() use ($data, $recvCallable) {
			return $this->callSync($data, $recvCallable);
		});
	}

	/**
	 * 同步发送
	 *
	 * 如果发送、接收失败，需要自己实现重试功能
	 *
	 * @param  string        $data         RAW DATA
	 * @param  callable|null $recvCallable 需要回调的函数，只有一个参数 $client，如果客户端recv时出现连接错误，需要您在这个函数内返回false，以帮助连接池处理异常的连接
	 * @return boolean|string              Recv or null
	 */
	public function callSync(string $data, callable $recvCallable = null)
	{
		try {

			return $this->execute(function($pool, $client) use ($data, $recvCallable) {
				// swoole_client->send
				// 发送的数据没有长度限制，发送的数据太大Socket缓存区塞满，底层会阻塞等待可写
				// 失败返回false，并设置$swoole_client->errCode
				if ($client->send($data) === false)
					throw new ConnectionException('Send failed');

				// 因为recv在连接失败，或包体错误的情况下回都返回空字符串，所以无法甄别是否是连接已经被关闭
				// 需要在recvCallable中判断回执的数据是不是真的完整， 然后返回false
				// 不然，只能等到下一轮的send才能判断是否连接中断
				$result = is_callable($recvCallable) ? call_user_func($recvCallable, $client) : null;

				if ($result === false)
					throw new ConnectionException('Recv failed');

				return $result;
			});

		} catch (Exception $e)
		{
			ConsoleLog::error($e);
			throw $e;
		}

	}

	/**
	 * 用于同步call的回调函数，只是简单是实现了连接异常的问题
	 * @return string|false recv data
	 */
	private function makeSimpleRecvCall()
	{
		return function($client) {
			$result = $client->recv();

			if ($result === '' || $result === false) // 简单判断recv是否返回正确
				return false;

			return $result;
		};
	}

}
