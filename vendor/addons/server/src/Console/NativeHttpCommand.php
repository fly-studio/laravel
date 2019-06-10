<?php

namespace Addons\Server\Console;

use Addons\Server\Kernel;
use Addons\Server\Structs\Config\Host;
use Illuminate\Contracts\Events\Dispatcher;
use Addons\Server\Servers\NativeHttpServer;
use Addons\Server\Structs\Config\ServerConfig;
use Addons\Server\Contracts\AbstractServerCommand;

class NativeHttpCommand extends AbstractServerCommand {

	protected $pidPath = '/tmp/native-http.pid';

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'server:native-http
							{--cert= : (string) The absolute path of ssl_cert}
							{--key= : (string) The absolute path of ssl_key}
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
		$server->setPidPath($this->getPidPath());

		$this->info('Create a native http server with: ' . $port);

		$kernel = app(Kernel::class);
		$kernel->handle($server);
		$this->info('Run it.');
		$kernel->run();

		return 0;
	}


}
