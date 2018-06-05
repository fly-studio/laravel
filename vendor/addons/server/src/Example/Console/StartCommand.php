<?php

namespace Addons\Server\Example\Console;

use Addons\Server\Kernel;
use Illuminate\Console\Command;
use Addons\Server\Servers\Server;
use Addons\Server\Structs\Config\Listen;
use Addons\Server\Protocols\Raw\Listener;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Structs\Config\ServerConfig;

class StartCommand extends Command {

	protected $signature = 'server:example
			{--host=127.0.0.1 : (string) IP/IPv6 of DNS listening: 0.0.0.0,::,0:0:0:0:0:0:0:0 for any, 127.0.0.1,::1 for local, ip for LAN or WAN}
			{--port=5901 : (number) Port of listening }
			{--daemon : Run the worker in daemon mode}
			{--user=nobody:nobody : (string) the user:group of swoole\'s process}
			';

	protected $description = 'Start a Example Server.';


	public function handle(Dispatcher $events)
	{
		$host = $this->option('host');
		$daemon = $this->option('daemon');
		$port = $this->option('port');
		list($user, $group) = explode(':', $this->option('user')) + [null, null];

		$server = new Server(ServerConfig::build(Listen::build($port, $host), compact('daemon', 'user', 'group')));
		$server->loadRoutes(__DIR__.'/../sockets.php', 'Addons\\Server\\Example\\Raw');
		$this->info('Create a tcp server with: ' . $port);

		$kernel = app(Kernel::class);
		$kernel->handle($server->capture(new Listener($server)));
		$this->info('Run it.');
		$kernel->run();

		return 0;
	}
}
