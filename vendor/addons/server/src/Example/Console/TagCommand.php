<?php

namespace Addons\Server\Example\Console;

use Addons\Server\Kernel;
use Illuminate\Console\Command;
use Addons\Server\Servers\Server;
use Addons\Server\Structs\Config\Host;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Protocols\TagProtobuf\Protocol;

class TagCommand extends Command {

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'server:tag-example
							{--host=127.0.0.1 : (string) IP/IPv6 of DNS listening: 0.0.0.0,::,0:0:0:0:0:0:0:0 for any, 127.0.0.1,::1 for local, ip for LAN or WAN}
							{--port=5902 : (number) Port of DNS listening }
							{--workers=1 : (number) Number of the workers running}
							{--daemon : Run the worker in daemon mode}
							{--user=nobody:nobody : (string) the user:group of swoole\'s process}
							';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Start a TAG Example Server.';

	public function handle(Dispatcher $events)
	{
		$host = $this->option('host');
		$port = $this->option('port');
		$worker_num = $this->option('workers');
		$daemon = $this->option('daemon');
		list($user, $group) = explode(':', $this->option('user')) + [null, null];

		$server = new Server(ServerConfig::build($host, $port, SWOOLE_SOCK_TCP, compact('daemon', 'user', 'group', 'worker_num')));
		$server->loadRoutes(__DIR__.'/../tag.php', 'Addons\\Server\\Example\\Tag');
		$server->capture(new Protocol());
		$this->info('Create a tcp server with: ' . $port);

		$kernel = app(Kernel::class);
		$kernel->handle($server);
		$this->info('Run it.');
		$kernel->run();

		return 0;
	}
}
