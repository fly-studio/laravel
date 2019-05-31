<?php

namespace Addons\Server\Contracts;

use Event;
use Illuminate\Console\Command;
use Addons\Server\Contracts\AbstractServer;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractServerCommand extends Command {

	protected $pidPath = null;

	public function __construct()
	{
		parent::__construct();

		$definition = $this->getDefinition();

		if (!$definition->hasOption('host'))
			$this->addOption('host', null, InputOption::VALUE_OPTIONAL, '(string) IP/IPv6 of server listening: 0.0.0.0,::,0:0:0:0:0:0:0:0 for any, 127.0.0.1,::1 for local, ip for LAN or WAN', '0.0.0.0');

		if (!$definition->hasOption('port'))
			$this->addOption('port', null, InputOption::VALUE_REQUIRED, '(number) Port of server listening', mt_rand(1025, 10240));

		if (!$definition->hasOption('workers'))
			$this->addOption('workers', null, InputOption::VALUE_OPTIONAL, '(number) Number of the workers running', 1);

		if (!$definition->hasOption('daemon'))
			$this->addOption('daemon', 'd', null, 'Run the worker in daemon mode');

		if (!$definition->hasOption('user'))
			$this->addOption('user', 'u', InputOption::VALUE_OPTIONAL, '(string) the user:group of swoole\'s process', 'nobody:nobody');


		$this->addOption('pid', null, InputOption::VALUE_OPTIONAL, 'A absolute path of the server\'s pid file', null);
		$this->addOption('reload', 'r', null, 'Reload this server');
		$this->addOption('stop', 's', null, 'Shutdown this server');
	}

    protected function execute(InputInterface $input, OutputInterface $output)
	{
		// 捕获Console参数reload、restart
		if ($input->hasParameterOption(['--reload', '-r'], true))
		{
			$this->reload();
			return;
		}

		if ($input->hasParameterOption(['--stop', '-s'], true))
		{
			$this->shutdown();
			return;
		}

        return $this->laravel->call([$this, 'handle']);
	}

	protected function reload()
	{
		$pid = $this->getPid();

		if (!empty($pid))
		{
			$this->info("kill -SIGUSR1 $pid :");
			if (posix_kill($pid, SIGUSR1))
				$this->warn("Reload success!");
			else
				$this->error("Reload fail, the server is not running.");
		} else
			$this->info('No running server!');
	}

	protected function shutdown()
	{
		$pid = $this->getPid();

		if (!empty($pid))
		{
			$this->info("kill -SIGTERM $pid :");
			if (posix_kill($pid, SIGTERM))
				$this->warn("Shutdown success!");
			else
				$this->error("Shutdown fail, the server is not running.");
		} else
			$this->info('No running server!');
	}

	protected function getPid()
	{
		$pidPath = $this->getPidPath();
		return file_exists($pidPath) ? intval(file_get_contents($pidPath)) : 0;
	}

	protected function getPidPath() {

		$path = $this->option('pid') ?? $this->pidPath;

		if (!empty($path) && !ends_with($path, '.pid'))
			throw new \InvalidArgumentException('"pid" file path must be end with ".pid"');

		return $path;
	}

}
