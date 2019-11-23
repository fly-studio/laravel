<?php

namespace Addons\Censor\File;

use Illuminate\Translation\FileLoader as Base;

class FileLoader extends Base {

	public function getPath(string $locale, string $group, string $namespace = null)
	{
		if (is_null($namespace) || $namespace == '*')
			return $this->getBasePath($this->path, $locale, $group);

		return $this->getNamespacedPath($locale, $group, $namespace);
	}

	public function getBasePath(string $path, string $locale, string $group)
	{
		return "{$path}/{$locale}/{$group}.php";
	}

	public function getNamespacedPath(string $locale, string $group, string $namespace = null)
	{
		if (isset($this->hints[$namespace])) {
			return $this->getBasePath($this->hints[$namespace], $locale, $group);
		} else {
			return "{$this->path}/vendor/{$namespace}/{$locale}/{$group}.php";
		}
	}

}
