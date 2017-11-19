<?php
namespace Addons\Smarty\View;

use Smarty;
use Illuminate\View;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\Contracts\View\Engine as EngineInterface;

class Engine implements EngineInterface
{

	protected $_config;
	protected $smarty;

	public function __construct($config)
	{
		// Create smarty object.
		$this->smarty = $smarty = new Smarty();

		$smarty->setTemplateDir($config['template_path']);
		$smarty->setCompileDir($config['compile_path']);
		$smarty->setCacheDir($config['cache_path']);

		foreach ($config['plugins_paths'] as $path) {
			$smarty->addPluginsDir($path);
		}
		foreach ($config['config_paths'] as $path) {
			$smarty->setConfigDir($path);
		}

		$smarty->debugging = $config['debugging'];
		$smarty->caching = $config['caching'];
		$smarty->cache_lifetime = $config['cache_lifetime'];
		$smarty->compile_check = $config['compile_check'];

		// set the escape_html flag from the configuration value
		//
		$smarty->escape_html = $config['escape_html'] ?: false;

		$smarty->left_delimiter = $config['left_delimiter'] ?: '{';
		$smarty->right_delimiter = $config['right_delimiter'] ?: '}';

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
			$this->smarty->assign($var, $val);

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

}