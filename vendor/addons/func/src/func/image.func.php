<?php

if (! function_exists('color_similar')) {
/**
 * 颜色是否相似
 * @param  integer  $rgb1  颜色1
 * @param  integer  $rgb2  颜色2
 * @param  integer $allowance 误差范围 正数
 * @return boolean
 */
function color_similar(int $rgb1, int $rgb2, int $allowance = 10) {
	$r1 = ($rgb1 >> 16) & 0xFF;
	$g1 = ($rgb1 >> 8) & 0xFF;
	$b1 = $rgb1 & 0xFF;
	$r2 = ($rgb2 >> 16) & 0xFF;
	$g2 = ($rgb2 >> 8) & 0xFF;
	$b2 = $rgb2 & 0xFF;
	return abs($r1 - $r2) < $value && abs($b1 - $b2) < $value && abs($g1 - $g2) < $value;
}
}

if (! function_exists('calculate_textbox')) {
/**
 * 计算使用指定字体渲染一段文本所需的rectangle
 * @param  integer $font_size 字体size
 * @param  float $font_angle 文字旋转角度
 * @param  string $font_file 字体文件
 * @param  string $text 文本
 * @return array
 */
function calculate_textbox(int $font_size, float $font_angle, string $font_file, string $text) {
	$box = imagettfbbox($font_size, $font_angle, $font_file, $text);
	if( !$box ) return false;
	$min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
	$max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
	$min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
	$max_y = max( array($box[1], $box[3], $box[5], $box[7]) );
	$width = ( $max_x - $min_x );
	$height = ( $max_y - $min_y );
	$left = abs( $min_x ) + $width;
	$top = abs( $min_y ) + $height;
	// to calculate the exact bounding box i write the text in a large image
	$img = @imagecreatetruecolor( $width << 2, $height << 2 );
	$white = imagecolorallocate( $img, 255, 255, 255 );
	$black = imagecolorallocate( $img, 0, 0, 0 );
	imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $black);
	// for sure the text is completely in the image!
	imagettftext( $img, $font_size, $font_angle, $left, $top, $white, $font_file, $text);
	// start scanning (0=> black => empty)
	$rleft  = $w4 = $width << 2;
	$rright = 0;
	$rbottom = 0;
	$rtop = $h4 = $height << 2;
	for( $x = 0; $x < $w4; $x++ )
		for( $y = 0; $y < $h4; $y++ )
			if( imagecolorat( $img, $x, $y ) ){
				$rleft = min( $rleft, $x );
				$rright = max( $rright, $x );
				$rtop = min( $rtop, $y );
				$rbottom = max( $rbottom, $y );
			}
	// destroy img and serve the result
	imagedestroy( $img );
	return array( 'left' => $left - $rleft,
			'top' => $top - $rtop,
			'width' => $rright - $rleft + 1,
			'height' => $rbottom - $rtop + 1 );
}
}

if (! function_exists('hex2rgb')) {
/**
 * 将颜色表达式，转换为RGB数组
 *
 * @param  string $colour 颜色的16进制，比如灰色的值为CCCCCC或CCC
 * @return [type]         [description]
 */
function hex2rgb(string $colour)
{
	$colour = preg_replace("/[^abcdef0-9]/i", "", $colour);
	if (strlen($colour) == 6)
		list($r, $g, $b) = str_split($colour, 2);
	elseif (strlen($colour) == 3)
		list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
	else
		return FALSE;
	$r = hexdec($r);$g = hexdec($g);$b = hexdec($b);
	return array(
		$r, $g, $b,
		'red' => $r, 'green' => $g, 'blue' => $b,
		'r' => $r, 'g' => $g, 'b' => $b,
	);
}
}

if (! function_exists('aspect_ratio')) {
/**
 * 等比缩放尺寸
 *
 * @param  float $width  原始宽
 * @param  float $height 原始高
 * @param  float $newWidth       需要缩放的宽
 * @param  float $newHeight      需要缩放的高
 * @return array                 返回等比缩放之后的宽高
 */
function aspect_ratio(float $width, float $height, float $newWidth = null, float $newHeight = null)
{
	empty($newWidth) && $newWidth = $width;
	empty($newHeight) && $newHeight = $height;

	if (!empty($width) && !empty($height))
	{
		$wh_ratio = $width / $height;
		$hw_ratio = $height / $width;
		$width = $newWidth;
		$height = $newHeight;
		if ($newWidth / $newHeight > $wh_ratio)
			$width = round($newHeight * $wh_ratio);
		if ($newHeight / $newWidth > $hw_ratio)
			$height = round($width * $hw_ratio);
	}
	return compact('width', 'height');
}
}
