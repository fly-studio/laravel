<?php

namespace Addons\Server\Structs;

use Co;
use Swoole\Coroutine\Channel;
use Addons\Func\Console\ConsoleLog;
use Addons\Server\Exceptions\ConnectionException;

abstract class Pool {

	protected $clients;
	protected $idlePool;
	protected $poolCount;
	protected $closed = false;
	protected $workingCount = 0;
	protected $lastWaitingCoroutines = [];

	public function __construct(int $poolCount)
	{
		$this->poolCount = $poolCount;
		$this->idlePool = new Channel($poolCount);
	}


	public function clients()
	{
		return $this->clients;
	}

	/**
	 * 连接池内所有连接
	 * 注意: 调用close之后，无法再connect，必须重新new TcpPool
	 *
	 * @param  int|integer $reconnectDelay 间隔多少时间检查是否断开连接，单位：ms
	 * @return
	 */
	public function connect(int $reconnectDelay = 500)
	{
		if ($this->closed)
			throw new RuntimeException('Pool was closed, you may create an new TcpPool instance.');

		go(function() use($reconnectDelay) {

			ConsoleLog::info('Try to connect to pool: '. $this->poolCount);

			while (!$this->closed) {

				// 发现失败就移除
				for($i = count($this->clients) - 1; $i >= 0; $i--)
				{
					$client = $this->clients[$i];

					if (!$this->isConnected($client))
					{
						array_splice($this->clients, $i, 1);
					}
				}

				$connectedClients = count($this->clients);
				$delta = $this->poolCount - $connectedClients;

				for($i = 0; $i < $delta; $i++)
				{
					$client = $this->doNewConnect();

					// 只有连接成功了才能加入clients和池
					if ($this->isConnected($client))
					{
						$this->clients[] = $client;
						$this->push($client);
						$connectedClients++;
					} else {
						unset($client);
					}
				}

				if ($connectedClients < $this->poolCount)
					ConsoleLog::info('connection information: '. $connectedClients . '/' . $this->poolCount. '. retry after '. $reconnectDelay.'ms.');

				Co::sleep($reconnectDelay / 1000);

			}
		});
	}

	/**
	 * 关闭所有连接，默认会等正在发送的数据发送完毕(也会等所有未发送的异步任务执行完毕)
	 *
	 * 注意：关闭之后无法再次connect
	 *
	 * @param  bool|boolean $force 强制关闭数据发送，不计丢失
	 * @return
	 */
	public function close(bool $force = false)
	{
		if (!$force) $this->waitFor();

		$this->closed = true;
		$this->workingCount = 0;
		$this->idlePool->close(); // 关闭池

		for($i = count($this->clients) - 1; $i >= 0; $i--)
		{
			$this->doClose($this->clients[$i], $force);
			unset($this->clients[$i]);
		}
	}

	abstract protected function doNewConnect();
	abstract protected function doClose($client, bool $force);
	abstract protected function isConnected($client);

	public function push($client)
	{
		$this->idlePool->push($client);
	}

	public function pop()
	{
		return $this->idlePool->pop();
	}

	/**
	 * 异步执行 $doSomething，因为是协程，需要使用resultCallback返回值，
	 *
	 * @param  callable $doSomething 执行函数，同execute
	 * @param  callable $resultCallback 异步结果返回call
	 */
	public function asyncExceute(callable $doSomething, callable $resultCallback = null)
	{
		go(function() use ($doSomething, $resultCallback) {
			$result = $this->execute($doSomething);
			if (is_callable($resultCallback)) call_user_func($resultCallback, $result);
		});
	}

	/**
	 * 在连接池中取出一个连接并执行$doSomething
	 *
	 * 注意:
	 * 在执行doSomething前会检查连接状态，如果连接异常，会一直重试到连接正确为止
	 * $client连接异常，connect函数会自动新增$client
	 * 会操作workingCount计数器，只有异步情况下才会出现 > 0
	 *
	 * @param  callable    $doSomething  执行函数 function($pool, $client) {} 如果在函数执行时，有连接问题，一定要抛出 Addons\Server\Exceptions\ConnectionException
	 * @return [type]                    $doSomething 执行的结果
	 */
	public function execute(callable $doSomething)
	{
		$this->addWorkingCount(); // +1

		$exception = null;
		$result = null;

		if (is_callable($doSomething))
		{
			// 一直重试
			while (!$this->closed) {

				// 取出连接池Client，pool如果为空，会被挂起

				$client = $this->pop();

				// 通道关闭
				if ($client === false)
					break;

				try {
					// 发送前检查连接状态，异常则获得取下一个连接
					if (!$this->isConnected($client))
						continue;

					$result = call_user_func($doSomething, $this, $client);

				} catch (\Throwable $e) {
					$exception = $e;
				}

				// 重新加入到连接池
				if (!($exception instanceof ConnectionException))
					$this->push($client);

				break;
			}
		}

		$this->addWorkingCount(-1); // -1 平衡

		if (!empty($exception))
			throw $exception;

		return $result;
	}

	public function workingCount()
	{
		return $this->workingCount;
	}

	public function isClosed()
	{
		return $this->closed;
	}

	/**
	 * 增删workingCount的方法，当为0时，恢复因waitFor而挂起的协程
	 * @param int|integer $value [description]
	 */
	protected function addWorkingCount(int $value = 1)
	{
		$this->workingCount += $value;

		if ($this->workingCount <= 0 && !empty($this->lastWaitingCoroutines))
		{
			foreach(array_reverse($this->lastWaitingCoroutines) as $id)
				Co::resume($id);

			$this->lastWaitingCoroutines = [];
		}
	}

	/**
	 * 等待所有异步任务结束，结束之前一直阻塞当前协程
	 * 只会阻塞调用本函数的协程，其它协程可以正常工作
	 *
	 * 比如批量加入很多异步sendAsync，然后使用本函数等待这些执行完毕，
	 * 原理：批量加入异步任务时，此时workingCount不为0，挂起当前协程，当workingCount为0时，唤醒协程。可以查看上面addworkingCount的代码
	 *
	 * 注意：必须运行在协程内，如果此函数上面都是同步call，执行本函数没有意义，因为同步call的workingCount肯定为0
	 *
	 * @example
	 * go(function() use ($list, $pool){
	 * 	foreach($list as $data)
	 * 	{
	 *  	$pool->run($data);
	 * 	}
	 *  $pool->waitFor(); // 挂起当前协程直到上面的callAsync处理完毕
	 * });
	 *
	 */
	public function waitFor()
	{
		$coroutineId = Co::getCid();

		if ($coroutineId < 0)
			throw new RuntimeException('Must run this in a Coroutine.');

		if ($this->workingCount() > 0) // 因为是单线程, 所以此处的不会像多线程一样需要原子
		{
			$this->lastWaitingCoroutines[] = $coroutineId;
			Co::yield();
		}

	}

}
