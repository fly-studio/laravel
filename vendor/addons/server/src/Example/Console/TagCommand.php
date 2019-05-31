<?php

namespace Addons\Server\Example\Console;

use Addons\Server\Kernel;
use Addons\Server\Servers\Server;
use Addons\Server\Structs\Config\Host;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Protocols\TagProtobuf\Protocol;
use Addons\Server\Contracts\AbstractServerCommand;

class TagCommand extends AbstractServerCommand {

	protected $pidPath = '/tmp/example-tag.pid';

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'server:tag-example
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
		$server->setPidPath($this->getPidPath());
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
