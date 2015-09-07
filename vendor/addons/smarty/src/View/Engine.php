<?php
namespace Addons\Smarty\View;

use Illuminate\View;
use Illuminate\View\Engines;
use Illuminate\View\Compilers\CompilerInterface;
use \Smarty;
class Engine implements Engines\EngineInterface
{

	protected $_config;
	protected $smarty;

	public function __construct($config)
	{
		$this->_config = $config;

		$caching = $this->config('caching');
		$cache_lifetime = $this->config('cache_lifetime');
		$compile_check = $this->config('compile_check', true);
		$debugging = $this->config('debugging');

		$template_path = $this->config('template_path');
		$compile_path = $this->config('compile_path');
		$cache_path = $this->config('cache_path');

		$plugins_paths = (array) $this->config('plugins_paths');
		$config_paths = (array) $this->config('config_paths');

		$escape_html = $this->config('escape_html', false);

		$left_delimiter = $this->config('left_delimiter', '{');
		$right_delimiter = $this->config('right_delimiter', '}');

		// Create smarty object.
		$this->smarty = $smarty = new \Smarty();

		$smarty->setTemplateDir($template_path);
		$smarty->setCompileDir($compile_path);
		$smarty->setCacheDir($cache_path);

		foreach ($plugins_paths as $path) {
			$smarty->addPluginsDir($path);
		}
		foreach ($config_paths as $path) {
			$smarty->setConfigDir($path);
		}

		$smarty->debugging = $debugging;
		$smarty->caching = $caching;
		$smarty->cache_lifetime = $cache_lifetime;
		$smarty->compile_check = $compile_check;

		// set the escape_html flag from the configuration value
		//
		$smarty->escape_html = $escape_html;

		$smarty->left_delimiter = $left_delimiter;
		$smarty->right_delimiter = $right_delimiter;

		$smarty->error_reporting = error_reporting() & ~E_NOTICE;
	}

	/**
	 * Get the evaluated contents of the view.
	 *
	 * @param string $path
	 * @param array $data
	 * @return string
	 */
	public function get($path, array $data = array())
	{
		return $this->evaluatePath($path, $data);
	}

	/**
	 * Get the evaluated contents of the view at the given path.
	 *
	 * @param string $path
	 * @param array $data
	 * @return string
	 */
	protected function evaluatePath($path, $data)
	{
		foreach ($data as $var => $val)
		{
			$this->smarty->assign($var, $val);
		}

		return $this->smarty->fetch($path);
	}

	public function getSmarty()
	{
		return $this->smarty;
	}

	/**
	 * Get the compiler implementation.
	 *
	 * @return Illuminate\View\Compilers\CompilerInterface
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

	/**
	 * Get package config.
	 */
	protected function config($key, $default = null)
	{
		$configKey = 'smarty.';
		return $this->_config->get($configKey . $key, $default);
	}
}