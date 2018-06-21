<?php

namespace Addons\Server\Listeners;

use Addons\Func\Console\ConsoleLog;
use Addons\Server\Contracts\AbstractListener;

class TaskListener extends AbstractListener {

	/**
	 * [task description]
	 * @param  [int]         $task_id       [description]
	 * @param  [int]         $src_worker_id [description]
	 * @param  [mixed]         $data          [description]
	 */
	public function onTask($task_id, $src_worker_id, $data)
	{
		ConsoleLog::info('Trigger a task: '. getmypid(). ' worker_id: '.$task_id);
	}

	/**
	 * [finish description]
	 * @param  [int]         $task_id [description]
	 * @param  [string]         $data    [description]
	 */
	public function onFinish($task_id, $data)
	{

	}
}
