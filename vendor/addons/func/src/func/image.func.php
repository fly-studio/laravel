<?php
/**
 * [calculate_textbox description]
 * @param  [type] $font_size  [description]
 * @param  [type] $font_angle [description]
 * @param  [type] $font_file  [description]
 * @param  [type] $text       [description]
 * @return [type]             [description]
 */
function calculate_textbox($font_size, $font_angle, $font_file, $text) { 
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


/**
 * 将颜色表达式，转换为RGB数组
 * 
 * @param  string $colour 颜色的16进制，比如灰色的值为CCCCCC或CCC
 * @return [type]         [description]
 */
function hex2rgb($colour)
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

/**
 * [aspect_ratio description]
 * @param  [type] $width  [description]
 * @param  [type] $height [description]
 * @param  [type] $newWidth       [description]
 * @param  [type] $newHeight      [description]
 * @return [type]                 [description]
 */
function aspect_ratio($width, $height, $newWidth = NULL, $newHeight = NULL)
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