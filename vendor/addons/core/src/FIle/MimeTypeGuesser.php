<?php
namespace Addons\Core\File;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser as BaseMimeTypeGuesser;
class MimeTypeGuesser extends MimeTypeExtensionGuesser,BaseMimeTypeGuesser {

	public function guess_by_ext($ext)
	{
		return array_search(strtolower($ext), $defaultExtensions);
	}	
}