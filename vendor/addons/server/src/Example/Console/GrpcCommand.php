<?php

namespace Addons\Server\Example\Console;

use Addons\Server\Kernel;
use Illuminate\Console\Command;
use Addons\Server\Servers\Http2Server;
use Addons\Server\Structs\Config\Host;
use Addons\Server\Protocols\Grpc\Listener;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Structs\Config\ServerConfig;

class GrpcCommand extends Command {

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'server:grpc-example
							{--cert= : The absolute path of ssl_cert}
							{--key= : The absolute path of ssl_key}
							{--host=127.0.0.1 : (string) IP/IPv6 of DNS listening: 0.0.0.0,::,0:0:0:0:0:0:0:0 for any, 127.0.0.1,::1 for local, ip for LAN or WAN}
							{--port=5903 : (number) Port of DNS listening }
							{--workers=1 : (number) Number of the workers running}
							{--daemon : Run the worker in daemon mode}
							{--user=nobody:nobody : (string) the user:group of swoole\'s process}
							';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Start a Grpc Example Server.';

	public function handle(Dispatcher $events)
	{
		$host = $this->option('host');
		$port = $this->option('port');
		$worker_num = $this->option('workers');
		$daemon = $this->option('daemon');
		list($user, $group) = explode(':', $this->option('user')) + [null, null];
		$ssl_cert_file = $this->option('cert');
		$ssl_key_file = $this->option('key');
		$ssl_method = SWOOLE_TLSv1_2_SERVER_METHOD;

		$server = new Http2Server(ServerConfig::build(Host::build($port, $host), compact('daemon', 'user', 'group', 'worker_num', 'ssl_cert_file', 'ssl_key_file', 'ssl_method')));
		$server->loadRoutes(__DIR__.'/../grpc.php', 'Addons\\Server\\Example\\Grpc');
		$this->info('Create a http2 server with: ' . $port);

		$kernel = app(Kernel::class);
		$kernel->handle($server->capture(new Listener($server)));
		$this->info('Run it.');
		$kernel->run();

		return 0;
	}
}
