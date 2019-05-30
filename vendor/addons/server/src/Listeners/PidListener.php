<?php

namespace Addons\Server\Listeners;

use Addons\Func\Console\ConsoleLog;
use Addons\Server\Contracts\AbstractListener;

class PidListener extends AbstractListener {

	protected $pidPath = null;
	protected $pidFp = null;

	public function pidPath(string $pidPath = null)
	{
		if (is_null($pidPath)) return $this->pidPath;

		$this->pidPath = $pidPath;
		return $this;
	}

	/**
	 * [start description]
	 */
	public function onStart()
	{
		ConsoleLog::info('write pid file.');

		if (!empty($this->pidPath))
		{
			$this->pidFp = fopen($this->pidPath, 'w');
			if (!flock($this->pidFp, LOCK_EX | LOCK_NB))
				throw new \RuntimeException('The server is running with the port: '.$this->server->config()->host()->port());

			fwrite($this->pidFp, getmypid());
		}
	}

	/**
	 * [shutdown description]
	 */
	public function onShutdown()
	{
		ConsoleLog::info('unlink pid file.');

		if (!empty($this->pidPath))
		{
			if (is_resource($this->pidFp))
			{
				flock($this->pidFp, LOCK_UN);
				fclose($this->pidFp);
			}

			@unlink($this->pidPath);
		}

	}
}
