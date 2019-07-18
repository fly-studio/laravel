<?php

namespace Addons\Server\Client;

use Co, Exception;
use RuntimeException;
use Swoole\Coroutine\Client;
use Swoole\Coroutine\Channel;
use Addons\Func\Console\ConsoleLog;

// 一个Swoole\Client的实现的Tcp连接池
class TcpPool {

	protected $host;
	protected $port;
	protected $poolCount;
	protected $serverConfig;
	protected $closed = false;

	protected $idlePool;
	protected $clients;

	protected $aliveCount = 0;
	protected $lastCoroutineId = -1;

	public function __construct(string $host, int $port, array $serverConfig = [], int $poolCount = 10)
	{
		$this->host = $host;
		$this->port = $port;
		$this->serverConfig = $serverConfig;
		$this->poolCount = $poolCount;

		$this->idlePool = new Channel($poolCount);
	}

	public function clients()
	{
		return $this->clients;
	}

	public function aliveCount()
	{
		return $this->aliveCount;
	}

	/**
	 * https://wiki.swoole.com/wiki/page/p-client/close.html
	 * 当一个swoole_client连接被close后不要再次发起connect。正确的做法是销毁当前的swoole_client，重新创建一个swoole_client并发起新的连接。
	 *
	 * @param  int|integer $reconnectDelay [description]
	 * @return [type]                      [description]
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
					if (!$client->isConnected() || $client->errCode > 0)
					{
						$client->close(true);
						array_splice($this->clients, $i, 1);
					}
				}

				$connectedClients = count($this->clients);
				$delta = $this->poolCount - $connectedClients;

				for($i = 0; $i < $delta; $i++)
				{
					$client = new Client(SWOOLE_SOCK_TCP);
					!empty($this->serverConfig) && $client->set($this->serverConfig);

					// 只有连接成功了才能加入clients和池
					if ($client->connect($this->host, $this->port))
					{
						$this->clients[] = $client;
						$this->idlePool->push($client);
						$connectedClients++;
					} else {
						// swoole_client在unset时会自动调用close方法关闭socket
						// https://wiki.swoole.com/wiki/page/29.html
						ConsoleLog::error('connect failed. Error: ['.$client->errCode. ']'.socket_strerror($client->errCode));
						$client->close();
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
	 * 关闭所有连接
	 * 注意: 关闭之后无法再次connect
	 * @param  bool|boolean $force [description]
	 * @return [type]              [description]
	 */
	public function close(bool $force = false)
	{
		$this->closed = true;
		$this->aliveCount = 0;
		$this->idlePool->close(); // 关闭池

		for($i = count($this->clients) - 1; $i >= 0; $i--)
		{
			$this->clients[$i]->close($force);
			unset($this->clients[$i]);
		}
	}

	protected function addAliveCount(int $value = 1)
	{
		$this->aliveCount += $value;

		if ($this->aliveCount <= 0 && $this->lastCoroutineId >= 0)
		{
			Co::resume($this->lastCoroutineId);
			$this->lastCoroutineId = 0;
		}
	}

	/**
	 * 等待所有异步任务结束，结束之前一直阻塞当前协程
	 * 只会阻塞调用本函数的协程，其它协程可以正常工作
	 *
	 * 比如批量加入很多异步sendAsync，然后使用本函数等待这些执行完毕，
	 * 原理：批量加入异步任务时，此时aliveCount不为0，挂起当前协程，当aliveCount为0时，唤醒协程。可以查看上面addAliveCount的代码
	 *
	 * 注意：必须运行在协程内，如果此函数上面都是同步call，执行本函数没有意义，因为同步call的aliveCount肯定为0
	 *
	 *
	 * @return [type] [description]
	 */
	public function waitFor()
	{
		$coroutineId = Co::getCid();

		if ($coroutineId < 0)
			throw new RuntimeException('Must run this in a Coroutine.');

		if ($this->aliveCount() > 0)
		{
			$this->lastCoroutineId = $coroutineId;
			Co::yield();
		}

	}

	/**
	 * 同步发送并接收数据
	 *
	 * 注意：使用本函数最好设置client的eol等参数，让swoole自动分割每个包
	 * 其次，这个只能返回一个包，如果需要多个返回的，只能用callAsync然后自己实现recv
	 *
	 * @param  string $data         [description]
	 * @param  bool   $needResponse 是否需要回执
	 * @return [type]               [description]
	 */
	public function call(string $data, bool $needResponse = true)
	{
		return $this->syncSend($data, $needResponse ? $this->makeSimpleRecvCall() : null);
	}

	/**
	 * 异步发送数据并接收回执
	 *
	 * 注意: $recvCallable执行的顺序是乱序的，因为连接池的缘故，server端返回的也不是按照顺序来，也就是说send顺序是：1 2 3，回复顺序可能是1 3 2
	 * 如果上下文有关联，执行的时候最好使用将上下文引入到匿名函数中 $pool->callAsync($data, function($client) use ($xxx) {$client->recv();} ); $xxx 表示上下文的变量
	 *
	 * @param  string        $data          [description]
	 * @param  callable|null $recvCallable [description]
	 * @return [type]                       [description]
	 */
	public function callAsync(string $data, callable $recvCallable = null)
	{
		return go(function() use ($data, $recvCallable) {
			return $this->syncSend($data, $recvCallable);
		});
	}

	private function syncSend(string $data, callable $recvCallable = null)
	{
		$this->addAliveCount();

		$result = null;

		while(!$this->closed) {
			// 取出连接池Client，pool如果为空，会被挂起
			$client = $this->idlePool->pop();
			// 通道关闭
			if ($client === false)
				break;

			try {
				// swoole_client->send
				// 发送的数据没有长度限制，发送的数据太大Socket缓存区塞满，底层会阻塞等待可写
				// 失败返回false，并设置$swoole_client->errCode
				// send错误，重试发送数据
				// 因为连接状态是自动检测的，所以失败的无需再次加入连接池
				if (!$client->isConnected() || $client->send($data) === false)
					continue;

				// 因为recv在连接失败，或包体错误的情况下回都返回空字符串，所以无法甄别是否是连接已经被关闭
				// 需要在recvCallable中判断回执的数据是不是真的完整， 然后返回false
				// 不然，只能等到下一轮的send才能判断是否连接中断
				$result = is_callable($recvCallable) ? call_user_func($recvCallable, $client) : null;

				if ($result === false)
					continue;

			} catch (Exception $e) { //捕获 $recvCallable 的异常

				ConsoleLog::error($e);
			}

			// 加入到连接池
			$this->idlePool->push($client);

			break;
		}

		$this->addAliveCount(-1);
		return $result;
	}

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
