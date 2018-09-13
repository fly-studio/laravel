<?php

namespace Addons\Server\Listeners;

use Addons\Func\Console\ConsoleLog;
use Addons\Server\Structs\ConnectBinder;
use Addons\Server\Structs\ServerOptions;
use Addons\Server\Contracts\AbstractListener;

class UdpListener extends AbstractListener {

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
			->client_port($client_info['port'])
			->connect_time(time())
			;

		$options->logger('info', 'UDP receive: ');
		$options->logger('debug', print_r($options->toArray(), true));
		$options->logger('hex', $data);

		$binder = new ConnectBinder($options);

		$this->recv($binder, $data);
	}

}
