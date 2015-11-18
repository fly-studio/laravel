<?php
namespace Addons\Core\Controllers;

use Illuminate\Routing\Controller;
//use Illuminate\Http\Response;
use PHPQRCode\QRcode;
use PHPQRCode\Constants;
use Image;
class QrController extends Controller {

	/**
	 * 输出二维码
	 * @param  [type]  $text      [description]
	 * @param  integer $size      [description]
	 * @param  string  $watermark [description]
	 * @return [type]             [description]
	 */
	public function index($text, $size = 25, $watermark = '')
	{
		return $this->png($text, $size, $watermark);
	}

	public function png($text, $size = 25, $watermark = '')
	{
		if (empty($watermark) || !file_exists(APPPATH.$watermark))
		{
			return response()->stream(function() use ($text, $size, $watermark){
				echo QRcode::png($text, FALSE, Constants::QR_ECLEVEL_M, $size, 0 );
			}, 200, ['Content-Type' => 'image/png']);
		}
		else
		{
			$watermark = APPPATH.$watermark;
			$driver = class_exists('Imagick') ? 'Imagick' : NULL;
			$tmp = tempnam(sys_get_temp_dir(), '.png');
			QRcode::png($text, $tmp, Constants::QR_ECLEVEL_M, $size, 0 );
			$img = Image::make($tmp);
			$wm = Image::make($watermark)->resize($img->width() * 0.2, $img->height() * 0.2);
			unlink($tmp);
			$img->insert($wm, 'center');
			return $img->response('png');
		}
	}

	public function svg($text, $element_id = FALSE, $width = FALSE, $size = FALSE)
	{
		return response(
			QRcode::svg($text, $element_id, FALSE, Constants::QR_ECLEVEL_H, $width, $size, 0 ),
			200,
			['Content-Type' => 'image/svg']
		);
	}
}