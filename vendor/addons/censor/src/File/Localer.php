<?php

namespace Addons\Censor\File;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\NamespacedItemResolver;

class Localer extends NamespacedItemResolver {

	/**
	 * The loader implementation.
	 *
	 * @var \Illuminate\Translation\FileLoader
	 */
	protected $loader;

	/**
	 * The default locale being used by the LocalePool.
	 *
	 * @var string
	 */
	protected $locale;

	/**
	 * The fallback locale used by the LocalePool.
	 *
	 * @var string
	 */
	protected $fallback;

	/**
	 * The array of loaded locale groups.
	 *
	 * @var array
	 */
	protected $loaded = [];

	/**
	 * Create a new LocalePool instance.
	 *
	 * @param  \Illuminate\Contracts\Translation\Loader  $loader
	 * @param  string  $locale
	 * @return void
	 */
	public function __construct(Loader $loader, $locale)
	{
		$this->loader = $loader;
		$this->locale = $locale;
	}

	/**
	 * Load the specified language group.
	 *
	 * @param  string  $namespace
	 * @param  string  $group
	 * @param  string  $locale
	 * @return void
	 */
	public function load($namespace, $group, $locale)
	{
		if ($this->isLoaded($namespace, $group, $locale)) {
			return;
		}

		// The loader is responsible for returning the array of language lines for the
		// given namespace, group, and locale. We'll set the lines in this array of
		// lines that have already been loaded so that we can easily access them.
		$lines = $this->loader->load($locale, $group, $namespace);

		$this->loaded[$namespace][$group][$locale] = $lines;
	}

	/**
	 * Add locale lines to the given locale.
	 *
	 * @param  array  $lines
	 * @param  string  $locale
	 * @param  string  $namespace
	 * @return void
	 */
	public function addLines(array $lines, $locale, $namespace = '*')
	{
		foreach ($lines as $key => $value) {
			list($group, $item) = explode('.', $key, 2);

			Arr::set($this->loaded, "$namespace.$group.$locale.$item", $value);
		}
	}

	/**
	 * Determine if a translation exists for a given locale.
	 *
	 * @param  string  $key
	 * @param  string|null  $locale
	 * @return bool
	 */
	public function hasForLocale($key, $locale = null)
	{
		return $this->has($key, $locale, false);
	}

	/**
	 * Determine if a translation exists.
	 *
	 * @param  string  $key
	 * @param  string|null  $locale
	 * @param  bool  $fallback
	 * @return bool
	 */
	public function has($key, $locale = null, $fallback = true)
	{
		return !is_null($this->get($key, [], $locale, $fallback));
	}

	/**
	 * Get the translation for the given key.
	 *
	 * @param  string  $key
	 * @param  array|null|Model   $replace
	 * @param  string|null  $locale
	 * @param  bool  $fallback
	 * @return string|array|null
	 */
	public function getLine($key, $replace = [], $locale = null, $fallback = true)
	{
		list($namespace, $group, $item) = $this->parseKey($key);

		// Here we will get the locale that should be used for the language line. If one
		// was not passed, we will use the default locales which was given to us when
		// the translator was instantiated. Then, we can load the lines and return.
		$locales = $fallback ? $this->localeArray($locale)
							 : [$locale ?: $this->locale];

		foreach ($locales as $locale) {
			if (! is_null($line = $this->read(
				$namespace, $group, $locale, $item, $replace
			))) {
				break;
			}
		}

		return isset($line) ? $line : null;
	}

	/**
	 * Get the array of locales to be checked.
	 *
	 * @param  string|null  $locale
	 * @return array
	 */
	protected function localeArray($locale)
	{
		return array_filter([$locale ?: $this->locale, $this->fallback]);
	}

	/**
	 * Determine if the given group has been loaded.
	 *
	 * @param  string  $namespace
	 * @param  string  $group
	 * @param  string  $locale
	 * @return bool
	 */
	protected function isLoaded($namespace, $group, $locale)
	{
		return isset($this->loaded[$namespace][$group][$locale]);
	}

	/**
	 * Retrieve a data out the loaded array.
	 *
	 * @param  string  $namespace
	 * @param  string  $group
	 * @param  string  $locale
	 * @param  string  $item
	 * @param  array   $replace
	 * @return string|array|null
	 */
	protected function read($namespace, $group, $locale, $item)
	{
		$this->load($namespace, $group, $locale);

		$data = Arr::get($this->loaded[$namespace][$group][$locale], $item);

		return $data;
	}

	/**
	 * Get the default locale being used.
	 *
	 * @return string
	 */
	public function locale()
	{
		return $this->getLocale();
	}

	/**
	 * Get the default locale being used.
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * Set the default locale.
	 *
	 * @param  string  $locale
	 * @return void
	 */
	public function setLocale($locale)
	{
		$this->locale = $locale;
	}

	/**
	 * Get the fallback locale being used.
	 *
	 * @return string
	 */
	public function getFallback()
	{
		return $this->fallback;
	}

	/**
	 * Set the fallback locale being used.
	 *
	 * @param  string  $fallback
	 * @return void
	 */
	public function setFallback($fallback)
	{
		$this->fallback = $fallback;
	}

	/**
	 * Add a new namespace to the loader.
	 *
	 * @param  string  $namespace
	 * @param  string  $hint
	 * @return void
	 */
	public function addNamespace($namespace, $hint)
	{
		$this->loader->addNamespace($namespace, $hint);
	}

	/**
	 * Get the language line loader implementation.
	 *
	 * @return \Illuminate\Contracts\Translation\Loader
	 */
	public function getLoader()
	{
		return $this->loader;
	}

	/**
	 * Parse a key into namespace, group, and item.
	 *
	 * @param  string  $key
	 * @return array
	 */
	public function parseKey($key)
	{
		$segments = parent::parseKey($key);

		if (is_null($segments[0])) {
			$segments[0] = '*';
		}

		return $segments;
	}

	public function getPath($key, $fallback = false)
	{
		list($namespace, $group, $item) = $this->parseKey($key);
		return $this->loader->getPath($fallback ? $this->fallback : $this->locale, $group, $namespace);
	}

}
