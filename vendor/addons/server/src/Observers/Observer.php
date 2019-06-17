<?php

namespace Addons\Server\Observers;

use Addons\Server\Contracts\AbstractObserver;

class Observer extends AbstractObserver {

	/**
	 * [start description]
	 * @param  \swoole_server $server [description]
	 */
	public function onStart(\swoole_server $server)
	{
		$this->trigger('onStart');
	}

	/**
	 * [shutdown description]
	 * @param  \swoole_server $server [description]
	 */
	public function onShutdown(\swoole_server $server)
	{
		$this->trigger('onShutdown');
	}

	/**
	 * [workerStart description]
	 * @param  \swoole_server $server    [description]
	 * @param  [int]         $worker_id [description]
	 */
	public function onWorkerStart(\swoole_server $server, $worker_id)
	{
		$this->trigger('onWorkerStart', $worker_id);
	}

	/**
	 * [workerStop description]
	 * @param  \swoole_server $server    [description]
	 * @param  [int]         $worker_id [description]
	 */
	public function onWorkerStop(\swoole_server $server, $worker_id)
	{
		$this->trigger('onWorkerStop', $worker_id);
	}

	/**
	 * [connect description]
	 * @param  \swoole_server $server     [description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 */
	public function onConnect(\swoole_server $server, $fd, $reactor_id)
	{
		$this->trigger('onConnect', $fd, $reactor_id);
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
		$this->trigger('onReceive', $fd, $reactor_id, $data);
	}

	/**
	 * [packet description]
	 * @param  \swoole_server $server      [description]
	 * @param  [string]         $data        [description]
	 * @param  [array]         $client_info [description]
	 */
	public function onPacket(\swoole_server $server, $data, $client_info)
	{
		$this->trigger('onPacket', $data, $client_info);
	}

	/**
	 * [close description]
	 * @param  \swoole_server $server     [description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 */
	public function onClose(\swoole_server $server, $fd, $reactor_id)
	{
		$this->trigger('onClose', $fd, $reactor_id);
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
		$this->trigger('onTask', $task_id, $src_worker_id, $data);
	}

	/**
	 * [finish description]
	 * @param  \swoole_server $server  [description]
	 * @param  [int]         $task_id [description]
	 * @param  [string]         $data    [description]
	 */
	public function onFinish(\swoole_server $server, $task_id, $data)
	{
		$this->trigger('onFinish', $task_id, $data);
	}

	/**
	 * [pipeMessage description]
	 * @param  \swoole_server $server         [description]
	 * @param  [int]         $from_worker_id [description]
	 * @param  [string]         $message        [description]
	 */
	public function onPipeMessage(\swoole_server $server, $from_worker_id, $message)
	{
		$this->trigger('onPipeMessage', $from_worker_id, $message);
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
		$this->trigger('onWorkerError', $worker_id, $worker_pid, $exit_code, $signal);
	}

	/**
	 * [managerStart description]
	 * @param  \swoole_server $server [description]
	 */
	public function onManagerStart(\swoole_server $server)
	{
		$this->trigger('onManagerStart');
	}

	/**
	 * [managerStop description]
	 * @param  \swoole_server $server [description]
	 */
	public function onManagerStop(\swoole_server $server)
	{
		$this->trigger('onManagerStop');
	}

}
