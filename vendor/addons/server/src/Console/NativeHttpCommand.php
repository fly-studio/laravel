<?php

namespace Addons\Server\Console;

use Addons\Server\Kernel;
use Illuminate\Console\Command;
use Addons\Server\Structs\Config\Host;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Servers\NativeHttpServer;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Protocols\Http\NativeProtocol;

class NativeHttpCommand extends Command {

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'server:native-http
							{--cert= : The absolute path of ssl_cert}
							{--key= : The absolute path of ssl_key}
							{--host=0.0.0.0 : (string) IP/IPv6 of DNS listening: 0.0.0.0,::,0:0:0:0:0:0:0:0 for any, 127.0.0.1,::1 for local, ip for LAN or WAN}
							{--port=8080 : (number) Port of DNS listening }
							{--workers=1 : (number) Number of the workers running}
							{--daemon : Run the worker in daemon mode}
							{--user=nobody:nobody : (string) the user:group of swoole\'s process}
							';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Start a Native Http Server.';

	public function handle(Dispatcher $events)
	{
		$host = $this->option('host');
		$port = $this->option('port');
		$worker_num = $this->option('workers');
		$daemon = $this->option('daemon');
		list($user, $group) = explode(':', $this->option('user')) + [null, null];
		$ssl_cert_file = $this->option('cert');
		$ssl_key_file = $this->option('key');
		$ssl_method = SWOOLE_TLSv1_2_METHOD;

		$server = new NativeHttpServer(ServerConfig::build($host, $port, SWOOLE_SOCK_TCP, compact('daemon', 'user', 'group', 'worker_num', 'ssl_cert_file', 'ssl_key_file', 'ssl_method')));

		$server->capture(new NativeProtocol());
		$this->info('Create a native http server with: ' . $port);

		$kernel = app(Kernel::class);
		$kernel->handle($server);
		$this->info('Run it.');
		$kernel->run();

		return 0;
	}
}
