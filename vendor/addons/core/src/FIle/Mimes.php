<?php
namespace Addons\Core\File;



class Mimes {

	private $mimes;

	public function __construct($guess = '')
	{
		$this->mimes = (array)config('mimes');
	}

	public function mime_by_ext($ext)
	{
		return array_search(strtolower($ext), $this->mimes);
	}

	public function ext_by_mime($mime)
	{
		return $this->mimes[$mime];
	}

	public function get_mimes()
	{
		return $this->mimes;
	}
}