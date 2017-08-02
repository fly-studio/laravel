<?php

namespace Addons\Core\Console;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleLog {

	static $consoleOutput;
	static $daemon = false;

	public static function write($type, $message)
	{
		if (!config('app.debug') && $type == 'debug') return;

		if (static::$daemon || !app()->runningInConsole())
		{
			logger()->$type($message);
		}
		else
		{
			if (empty(static::$consoleOutput))
				static::$consoleOutput = new OutputStyle(new ArgvInput(), new ConsoleOutput());

			$type = str_replace(['debug', 'info'], ['text', 'note'], $type);
			static::$consoleOutput->$type($message);
		}
	}

	public static function error($message)
	{
		return static::write('error', $message);
	}

	public static function success($message)
	{
		return static::write('success', $message);
	}

	public static function info($message)
	{
		return static::write('info', $message);
	}

	public static function warning($message)
	{
		return static::write('warning', $message);
	}

	public static function debug($message)
	{
		return static::write('debug', $message);
	}

	public static function hex($message)
	{
		return static::debug(hex_dump($message));
	}

}
