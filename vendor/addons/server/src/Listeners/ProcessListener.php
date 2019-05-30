<?php

namespace Addons\Server\Listeners;

use Addons\Func\Console\ConsoleLog;
use Addons\Server\Contracts\AbstractListener;

class ProcessListener extends AbstractListener {

	/**
	 * [start description]
	 */
	public function onStart()
	{
		ConsoleLog::info('starting...');
	}

	/**
	 * [shutdown description]
	 */
	public function onShutdown()
	{
		ConsoleLog::info('shutdown.');
	}

	/**
	 * [workerStart description]
	 * @param  [int]         $worker_id [description]
	 */
	public function onWorkerStart($worker_id)
	{
		if ($this->server->taskworker)
			ConsoleLog::info('starting a task: [PID: '. getmypid(). '] task_id: '.($worker_id - $this->server->setting['worker_num']));
		else
			ConsoleLog::info('starting a work: [PID: '. getmypid(). '] worker_id: '.$worker_id);
	}

	/**
	 * [workerStop description]
	 * @param  [int]         $worker_id [description]
	 */
	public function onWorkerStop($worker_id)
	{
		if ($this->server->taskworker)
			ConsoleLog::info('stop a task: [PID: '. getmypid(). '] task_id: '.($worker_id - $this->server->setting['worker_num']));
		else
			ConsoleLog::info('stop a work: [PID: '. getmypid(). '] worker_id: '.$worker_id);
	}

	/**
	 * [managerStart description]
	 */
	public function onManagerStart()
	{

	}

	/**
	 * [managerStop description]
	 */
	public function onManagerStop()
	{

	}

	/**
	 * [pipeMessage description]
	 * @param  [int]         $from_worker_id [description]
	 * @param  [string]         $message        [description]
	 */
	public function onPipeMessage($from_worker_id, $message)
	{

	}

	/**
	 * [workerError description]
	 * @param  [int]         $worker_id  [description]
	 * @param  [int]         $worker_pid [description]
	 * @param  [int]         $exit_code  [description]
	 * @param  [int]         $signal     [description]
	 */
	public function onWorkerError($worker_id, $worker_pid, $exit_code, $signal)
	{
		ConsoleLog::info('worker error: '. getmypid(). ' worker_id: '.$worker_id. ' exit_code: '. $exit_code.' signal: '.$signal);
	}
}
