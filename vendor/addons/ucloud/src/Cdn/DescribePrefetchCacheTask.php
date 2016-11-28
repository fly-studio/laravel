<?php
namespace Addons\Ucloud\Cdn;

use Addons\Ucloud\Factory;
class DescribePrefetchCacheTask {
	private $factory;
	private $taskId, $beginTime, $endTime, $status, $offset, $limit;

	public function __construct(Factory $factory)
	{
		$this->factory = $factory;
	}

	public function setTaskId($taskId)
	{
		$this->taskId = $taskId;
		return $this;
	}

	public function setBeginTime($beginTime)
	{
		$this->beginTime = $beginTime;
		return $this;
	}
	public function setEndTime($endTime)
	{
		$this->endTime = $endTime;
		return $this;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	public function setOffset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	public function setLimit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	public function handle()
	{
		$params = [];
		!empty($this->taskId) && $params['TaskId'] = $this->taskId;
		!empty($this->beginTime) && $params['BeginTime'] = $this->beginTime;
		!empty($this->endTime) && $params['EndTime'] = $this->endTime;
		!empty($this->status) && $params['Status'] = $this->status;
		!empty($this->offset) && $params['Offset'] = $this->offset;
		!empty($this->limit) && $params['Limit'] = $this->limit;

		$result = $this->factory->http_get('DescribePrefetchCacheTask', $params);

		return $result['RetCode'] == 0 ? $result['TaskSet'] : false;
	}
}