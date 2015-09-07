<?php
namespace Addons\Core\Controllers;

use Illuminate\Routing\Controller;
//use Illuminate\Http\Response;

class PlaceholderController extends Controller {

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
		$cache_filepath = storage_path('placeholders/'.md5(serialize(func_get_args())).'.png');
		if (!file_exists($cache_filepath))
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
			$fontbox = calculate_textbox($fontsize, 0, $font, $text);
			// Generate text
			function_exists('imageantialias') && imageantialias($image, true);
			imagettftext($image, $fontsize, 0, ceil(($width - $fontbox['width'] ) / 2 + $fontbox['left']), ceil(($height - $fontbox['height']  ) / 2 + $fontbox['top']), $fontcolor, $font, $text);
			// Render image
			imagepng($image, $cache_filepath);
			imagedestroy($image);
		}
		return response()->preview($cache_filepath, ['Content-Type' => 'image/png']);
	}
}