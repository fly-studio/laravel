<?php
namespace Addons\Core\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Response;

class PlaceholderController extends Controller {

	public function __construct()
	{
		
	}
	/**
	 * 输出图片
	 * 
	 * @param  string $size     [description]
	 * @param  string $bgcolor  [description]
	 * @param  string $color    [description]
	 * @param  string $text     [description]
	 * @param  [type] $fontsize [description]
	 * @return [type]           [description]
	 */
	public function index($size = '100x100', $bgcolor = 'ccc', $color = '555', $text = '', $fontsize = NULL)
	{

		
		// Dimensions
		list($width, $height) = explode('x', $size);
		empty($height) && $height = $width;
		$bgcolor    = hex2rgb($bgcolor);
		$color         = hex2rgb($color);
		empty($text) && $text = $width . ' x ' .$height;

		$hash_key = md5(serialize(compact('width','height','bgcolor','color','text')));

		// Create image
		$image = imagecreate($width, $height);

		// Colours
		$setbg = imagecolorallocate($image, $bgcolor['r'], $bgcolor['g'], $bgcolor['b']);
		$fontcolor = imagecolorallocate($image, $color['r'], $color['g'], $color['b']);

		// Text positioning
		empty($fontsize) && $fontsize = ($width > $height) ? ($height / 10) : ($width / 10) ;
		$font = __DIR__.'/../../../fonts/msyh.ttf';
		$fontbox = imagettfbbox($fontsize, 0, $font, $text);
		// Generate text
		imageantialias($image, true);
		imagettftext($image, $fontsize, 0, ceil(($width - $fontbox[2]) / 2), ceil(($height - $fontbox[3]) / 2), $fontcolor, $font, $text);

		return response()->stream(function() use($image) {
			@ob_clean(); //在输出文件前,清除缓冲区
			// Render image
			imagepng($image);
			imagedestroy($image);
		}, 200, ['Content-Type' => 'image/png']);
	}
}