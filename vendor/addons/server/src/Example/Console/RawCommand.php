<?php

namespace Addons\Server\Example\Console;

use Addons\Server\Kernel;
use Addons\Server\Servers\Server;
use Addons\Server\Structs\Config\Host;
use Addons\Server\Protocols\Raw\Protocol;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Contracts\AbstractServerCommand;

class RawCommand extends AbstractServerCommand {

	protected $pidPath = '/tmp/example-raw.pid';

	protected $signature = 'server:raw-example
			';

	protected $description = 'Start a RAW Example Server.';


	public function handle(Dispatcher $events)
	{
		$host = $this->option('host');
		$daemon = $this->option('daemon');
		$port = $this->option('port');
		list($user, $group) = explode(':', $this->option('user')) + [null, null];

		$server = new Server(ServerConfig::build($host, $port, SWOOLE_SOCK_TCP, compact('daemon', 'user', 'group')));
		$server->setPidPath($this->getPidPath());
		$server->loadRoutes(__DIR__.'/../raw.php', 'Addons\\Server\\Example\\Raw');
		$server->capture(new Protocol());
		$this->info('Create a tcp server with: ' . $port);

		$kernel = app(Kernel::class);
		$kernel->handle($server);
		$this->info('Run it.');
		$kernel->run();

		return 0;
	}
}
