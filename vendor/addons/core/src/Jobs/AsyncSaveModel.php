<?php

namespace Addons\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AsyncSaveModel implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $className;
	protected $saveList;
	protected $connectionName;

	/**
	 * Save models in queue(async)
	 *
	 * @param string $className Model
	 * @param array  $saveList  [[id => xxx, field1 => value1]]
	 */
	public function __construct(string $className, array $saveList, string $connectionName = null)
	{
		if (!is_subclass_of($className, Model::class))
			throw new \RuntimeException($className .' is not instanceOf \Illuminate\Database\Eloquent\Model');

		$this->className = $className;
		$this->saveList = base64_encode(serialize($saveList));
		$this->connectionName = $connectionName;
	}

	public function handle()
	{
		$class = $this->className;
		$instance = new $class();

		$this->saveList = unserialize(base64_decode($this->saveList));

		foreach($this->saveList as $data)
		{
			$model = $instance->newInstance([], true);
			$model->setConnection($this->connectionName ?? $instance->getConnectionName());
			$model->forceFill($data);
			$model->save();
		}
	}

}
