<?php
namespace Addons\Core;

use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Core {
	use Macroable;
	/**
	 * Make the place-holder replacements on a line.
	 *
	 * @param  string  $line
	 * @param  array   $replace
	 * @return string
	 */
	public function __($line, array $replace)
	{
		$replace = array_keyflatten($replace, '.', ':');
		$replace = $this->sortReplacements($replace);

		return strtr($line, (array)$replace->toArray());
	}

	/**
	 * Sort the replacements array.
	 *
	 * @param  array  $replace
	 * @return array
	 */
	private function sortReplacements(array $replace)
	{
		return (new Collection($replace))->sortBy(function ($value, $key) {
			return mb_strlen($key) * -1;
		});
	}
}