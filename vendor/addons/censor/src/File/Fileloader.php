<?php

namespace Addons\Censor\File;

use Illuminate\Translation\FileLoader as Base;

class FileLoader extends Base {

	public function getPath($locale, $group, $namespace = null)
	{
		if (is_null($namespace) || $namespace == '*')
			return $this->getBasePath($this->path, $locale, $group);

		return $this->getNamespacedPath($locale, $group, $namespace);
	}

	public function getBasePath($path, $locale, $group)
	{
		return "{$path}/{$locale}/{$group}.php";
	}

	public function getNamespacedPath($locale, $group, $namespace)
	{
		if (isset($this->hints[$namespace])) {
			return $this->getBasePath($this->hints[$namespace], $locale, $group);
		} else {
			return "{$this->path}/vendor/{$namespace}/{$locale}/{$group}.php";
		}
	}

}
