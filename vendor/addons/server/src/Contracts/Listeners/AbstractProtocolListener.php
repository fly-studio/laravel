<?php

namespace Addons\Server\Contracts\Listeners;

use RuntimeException;
use Addons\Server\Servers\Fire;
use Addons\Server\Servers\Server;
use Addons\Server\Console\ConsoleLog;
use Addons\Server\Structs\ConnectPool;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractRequest;
use Addons\Server\Contracts\AbstractResponse;
use Addons\Server\Contracts\Listeners\IListener;

abstract class AbstractProtocolListener implements IListener {

	protected $server;
	protected $pool = null;
	protected $logPrefix = '[Server]';

	public function __construct(Server $server)
	{
		$this->server = $server;
		$this->pool = new ConnectPool();
	}

	/**
	 * 可自定义该方法，分析数据之后返回不同的Request
	 *
	 * @param  ServerOptions $options [description]
	 * @param  [type]  $raw     [description]
	 * @return [type]           [description]
	 */
	abstract public function doRequest(ServerOptions $options, ?string $raw) : AbstractRequest;
	abstract public function doResponse(ServerOptions $options, AbstractRequest $request, ?string $raw) : AbstractResponse;

	protected function makeFire(ServerOptions $options)
	{
		return new Fire($options, [$this, 'doRequest'], [$this, 'doResponse']);
	}

	protected function makeServerOptions($fd, $reactor_id)
	{
		//TCP only
		$client_info = $this->server->getClientInfo($fd, $reactor_id);

		$options = new ServerOptions($this->server);
		$options
			->unique($fd)
			->file_descriptor($fd)
			->socket_type($client_info['socket_type'])
			->reactor_id($reactor_id)
			->server_port($client_info['server_port'])
			->client_ip($client_info['remote_ip'])
			->client_port($client_info['remote_port']);
		return $options;
	}

	/**
	 * [start description]
	 */
	public function onStart()
	{
		ConsoleLog::info($this->logPrefix.' starting...');
	}

	/**
	 * [shutdown description]
	 */
	public function onShutdown()
	{
		ConsoleLog::info($this->logPrefix.' shutdown.');
	}

	/**
	 * [workerStart description]
	 * @param  [int]         $worker_id [description]
	 */
	public function onWorkerStart($worker_id)
	{
		if ($this->server->taskworker)
			ConsoleLog::info($this->logPrefix.' starting a task: '. getmypid(). ' task_id: '.($worker_id - $this->server->setting['worker_num']));
		else
			ConsoleLog::info($this->logPrefix.' starting a work: '. getmypid(). ' worker_id: '.$worker_id);
	}

	/**
	 * [workerStop description]
	 * @param  [int]         $worker_id [description]
	 */
	public function onWorkerStop($worker_id)
	{
		if ($this->server->taskworker)
			ConsoleLog::info($this->logPrefix.' stop a task: '. getmypid(). ' task_id: '.($worker_id - $this->server->setting['worker_num']));
		else
			ConsoleLog::info($this->logPrefix.' stop a work: '. getmypid(). ' worker_id: '.$worker_id);
	}

	/**
	 * [connect description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 */
	public function onConnect($fd, $reactor_id)
	{
		$options = $this->makeServerOptions($fd, $reactor_id);

		$this->pool->set($options->unique(), $options);

		$options->logger('info', sprintf('%s TCP Server connect from [%s:%s]', $this->logPrefix, $options->client_ip(), $options->client_port()));
	}

	/**
	 * [receive description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 * @param  [mixed]         $data       [description]
	 */
	public function onReceive($fd, $reactor_id, $data)
	{
		$options = $this->pool->get($fd);
		if (empty($options))
			return;

		$options->logger('info', $this->logPrefix.' TCP receive: ');
		$options->logger('debug', print_r($options->toArray(), true));
		$options->logger('hex', $data);

		$this->fire($options, $data);
	}

	/**
	 * [packet description]
	 * @param  [string]         $data        [description]
	 * @param  [array]         $client_info [description]
	 */
	public function onPacket($data, $client_info)
	{
		$fd = unpack('L', pack('N', ip2long($client_info['address'])))[1];
		$unique = unpack('Q', pack('Nnn', ip2long($client_info['address']), $client_info['port'], 0))[1];
		$reactor_id = ($client_info['server_socket'] << 16) + $client_info['port'];

		// UDP 不创建连接， 不加入连接池
		$options = new ServerOptions($this->server);
		$options
			->unique($unique)
			->file_descriptor($fd)
			->server_socket($client_info['server_socket'])
			->socket_type(SWOOLE_SOCK_UDP)
			->reactor_id($reactor_id)
			->server_port($client_info['server_port'])
			->client_ip($client_info['address'])
			->client_port($client_info['port']);

		$options->logger('info', $this->logPrefix.' UDP receive: ');
		$options->logger('debug', print_r($options->toArray(), true));
		$options->logger('hex', $data);

		$this->fire($options, $data);
	}

	/**
	 * [close description]
	 * @param  [int]         $fd         [description]
	 * @param  [int]         $reactor_id [description]
	 */
	public function onClose($fd, $reactor_id)
	{
		//Only TCP
		$this->pool->remove($fd);
	}

	/**
	 * [bufferFull description]
	 * @param  [int]         $fd     [description]
	 */
	public function onBufferFull($fd)
	{

	}

	/**
	 * [bufferEmpty description]
	 * @param  [int]         $fd   [description]
	 */
	public function onBufferEmpty($fd)
	{

	}

	/**
	 * [task description]
	 * @param  [int]         $task_id       [description]
	 * @param  [int]         $src_worker_id [description]
	 * @param  [mixed]         $data          [description]
	 */
	public function onTask($task_id, $src_worker_id, $data)
	{
		ConsoleLog::info($this->logPrefix.' trigger a task: '. getmypid(). ' worker_id: '.$task_id);
	}

	/**
	 * [finish description]
	 * @param  [int]         $task_id [description]
	 * @param  [string]         $data    [description]
	 */
	public function onFinish($task_id, $data)
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
		ConsoleLog::info($this->logPrefix.' worker error: '. getmypid(). ' worker_id: '.$worker_id. ' exit_code: '. $exit_code.' signal: '.$signal);
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

	protected function fire(ServerOptions $options, ?string $raw)
	{
		$fire = $this->makeFire($options);
		$fire->handle($raw);
	}

}
