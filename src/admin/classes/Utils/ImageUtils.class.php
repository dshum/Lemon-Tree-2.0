<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008                                          *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	class ImageUtils
	{
		public static function resizeAndCopyImage($source, $destination, $new_width, $new_height, $quality = 60)
		{
			if(!is_readable($source)) return false;

			list($width, $height, $type, $attr) = getimagesize($source);
			$truecolor = false;

			switch ($type) {
				case 1:
					$image = imagecreatefromgif($source);
					$truecolor = true;
					break;
				case 2:
					$image = imagecreatefromjpeg($source);
					$truecolor = imageistruecolor($image);
					break;
				case 3:
					$image = imagecreatefrompng($source);
					$truecolor = imageistruecolor($image);
					break;
				default:
					return false;
					break;
			}

			if($new_width > 0 && $new_height <= 0 && $new_width < $width) {
				$new_height = round($height * $new_width / $width);
			} elseif($new_height > 0 && $new_width <= 0 && $new_height < $height) {
				$new_width = round($width * $new_height / $height);
			} elseif($new_width > 0 && $new_height > 0 && ($new_width < $width || $new_height < $height)) {
				if($new_width / $width < $new_height / $height) {
					$new_height = round($height * $new_width / $width);
				} else {
					$new_width = round($width * $new_height / $height);
				}
			} else {
				copy($source, $destination);
				return true;
			}

			if(function_exists("imagecreatetruecolor") && $truecolor) {
				$new_image = imagecreatetruecolor($new_width, $new_height);
			} else {
				$new_image = imagecreate($new_width, $new_height);
			}

			imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagedestroy($image);
			switch($type) {
				case 1:
					imagegif($new_image, $destination);
					break;
				case 2:
					imagejpeg($new_image, $destination, $quality);
					break;
				case 3:
					imagepng($new_image, $destination);
					break;
				default:
					break;
			}
			imagedestroy($new_image);
			return true;
		}
	}
?>