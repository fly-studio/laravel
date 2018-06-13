<?php

namespace Addons\Server\Servers\Observer;

use RuntimeException;
use Addons\Server\Servers\Server;
use Addons\Func\Console\ConsoleLog;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Protocols\Analyzers\ServiceFactory;
use Addons\Server\Contracts\Listeners\AbstractProtocolListener;

class Observer {

	protected $server;
	protected $listener = null;

	public function __construct(Server $server)
	{
		$this->server = $server;
	}

	public function setListener(AbstractProtocolListener $listener)
	{
		$this->listener = $listener;
		return false;
	}

	public function getListener() : ?AbstractProtocolListener
	{
		return $this->listener;
	}

	/**
	 * [start description]
	 * @param  \swoole_server $server [description]
	 */
	public function onStart(\swoole_server $server)
	{
		$this->listener->onStart();
	}

	/**
	 * [shutdown description]
	 * @param  \swoole_server $server [description]
	 */
	public function onShutdown(\swoole_server $server)
	{
		$this->listener->onShutdown();
	}

	/**
	 * [workerStart description]
	 * @param  \swoole_server $server    [description]
	 * @param  [int]         $worker_id [description]
	 */
	public function onWorkerStart(\swoole_server $server, $worker_id)
	{
		$this->listener->onWorkerStart($worker_id);
	}

	/**
	 * [workerStop description]
	 * @param  \swoole_server $server    [description]
	 * @param  [int]         $worker_id [description]
	 */
	public function onWorkerStop(\swoole_server $server, $worker_id)
	{
		$this->listener->onWorkerStop($worker_id);
	}

	/**
	 * [connect description]
	 * @param  \swoole_server $server     [description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 */
	public function onConnect(\swoole_server $server, $fd, $reactor_id)
	{
		$this->listener->onConnect($fd, $reactor_id);
	}

	/**
	 * [receive description]
	 * @param  \swoole_server $server     [description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 * @param  [mixed]         $data       [description]
	 */
	public function onReceive(\swoole_server $server, $fd, $reactor_id, $data)
	{
		$this->listener->onReceive($fd, $reactor_id, $data);
	}

	/**
	 * [packet description]
	 * @param  \swoole_server $server      [description]
	 * @param  [string]         $data        [description]
	 * @param  [array]         $client_info [description]
	 */
	public function onPacket(\swoole_server $server, $data, $client_info)
	{
		$this->listener->onPacket($data, $client_info);
	}

	/**
	 * [close description]
	 * @param  \swoole_server $server     [description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 */
	public function onClose(\swoole_server $server, $fd, $reactor_id)
	{
		$this->listener->onClose($fd, $reactor_id);
	}

	/**
	 * [bufferFull description]
	 * @param  \Swoole\Server $server [description]
	 * @param  [int]         $fd     [description]
	 */
	public function onBufferFull(\Swoole\Server $server, $fd)
	{
		$this->listener->onBufferFull($fd);
	}

	/**
	 * [bufferEmpty description]
	 * @param  \Swoole\Server $serv [description]
	 * @param  [int]         $fd   [description]
	 */
	public function onBufferEmpty(\Swoole\Server $serv, $fd)
	{
		$this->listener->onBufferEmpty($fd);
	}

	/**
	 * [task description]
	 * @param  \swoole_server $server        [description]
	 * @param  [int]         $task_id       [description]
	 * @param  [int]         $src_worker_id [description]
	 * @param  [mixed]         $data          [description]
	 */
	public function onTask(\swoole_server $server, $task_id, $src_worker_id, $data)
	{
		$this->listener->onTask($task_id, $src_worker_id, $data);
	}

	/**
	 * [finish description]
	 * @param  \swoole_server $server  [description]
	 * @param  [int]         $task_id [description]
	 * @param  [string]         $data    [description]
	 */
	public function onFinish(\swoole_server $server, $task_id, $data)
	{
		$this->listener->onFinish($task_id, $data);
	}

	/**
	 * [pipeMessage description]
	 * @param  \swoole_server $server         [description]
	 * @param  [int]         $from_worker_id [description]
	 * @param  [string]         $message        [description]
	 */
	public function onPipeMessage(\swoole_server $server, $from_worker_id, $message)
	{
		$this->listener->onPipeMessage($from_worker_id, $message);
	}

	/**
	 * [workerError description]
	 * @param  \swoole_server $server     [description]
	 * @param  [int]         $worker_id  [description]
	 * @param  [int]         $worker_pid [description]
	 * @param  [int]         $exit_code  [description]
	 * @param  [int]         $signal     [description]
	 */
	public function onWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code, $signal)
	{
		$this->listener->onWorkerError($worker_id, $worker_pid, $exit_code, $signal);
	}

	/**
	 * [managerStart description]
	 * @param  \swoole_server $server [description]
	 */
	public function onManagerStart(\swoole_server $server)
	{
		$this->listener->onManagerStart();
	}

	/**
	 * [managerStop description]
	 * @param  \swoole_server $server [description]
	 */
	public function onManagerStop(\swoole_server $server)
	{
		$this->listener->onManagerStop();
	}

}
