<?php
// ------------------------------------------------------------
// UTILS
// ------------------------------------------------------------

function processFolder($path, $max_width, $max_height, $recursive_mode){
	$only_dir_name = trim(basename($path).PHP_EOL);
	echo Console::green("\r\nDirectory name: ".$only_dir_name);
	echo Console::green("\r\n========================================");

	$images = glob($path . "\*.{jpg,png}", GLOB_BRACE);
	foreach ($images as $key => $image_path) {
		resizeImage($image_path, $max_width, $max_height);
	}
	echo Console::green("========================================\r\n");
	echo Console::green("Processed file count ($only_dir_name): ".count($images)." \r\n");

	if ($recursive_mode) {
		$dirs = glob($path . '/*' , GLOB_ONLYDIR);
		foreach ($dirs as $key => $dir) {
			$only_dir_name = basename($dir).PHP_EOL;
			if (trim($only_dir_name) !== "output") {
				processFolder($dir, $max_width, $max_height, $recursive_mode);
			}
		}
	}
}


function resizeImage($image_path, $max_width, $max_height){
	checkExists($image_path);
	$original_info = getimagesize($image_path);
	$file_size = (filesize($image_path))/1024;
	$original_w = $original_info[0];
	$original_h = $original_info[1];
	echo "\r\n----------------------------------------\r\n";
	echo "Original image info:\r\n  path:"; 
  echo Console::yellow(" $image_path");
  echo "\r\n  width: $original_w
  height: $original_h
  size: $file_size kb";
	$size = getNewImageSize($original_w, $original_h, $max_width, $max_height);
	$new_filename = makeNewFilename($image_path, $size);
	$resized = resizeAndSave($image_path, $size, $original_w, $original_h, $new_filename);
}

function checkExists($image_path){
	if (!file_exists($image_path)) {
		echo Console::red("ERROR: $image_path - file not found");
		exit();
	}
	if(!isImageExtensionAllowed($image_path)){
		echo Console::red("ERROR: $image_path - file extension is not allowed. Try to load jpg or png image file");
		exit();
	}
}
function isImageExtensionAllowed($image_path){
	$allowed_extensions = array('image/jpeg','image/png');
	$original_info = getimagesize($image_path);
	if (in_array($original_info['mime'], $allowed_extensions))
		return true;
	return false;
}

function getNewImageSize($original_w, $original_h, $max_width, $max_height){
	$w = 0;
	$h = 0;
	if ($original_w > $original_h) {
		$ratio = $original_w / $original_h;
		$w = $max_width;
		$h = ceil($w / $ratio);
		if ($h > $max_height) {
			$w = ceil($max_height * $ratio);
			$h = $max_height;
		}
	}
	if ($original_w < $original_h) {
		$ratio = $original_h / $original_w;
		$h = $max_height;
		$w = ceil($h / $ratio);
		if ($w > $max_width) {
			$h = ceil($max_width * $ratio);
			$w = $max_width;
		}
	}
	return [
		'w'=> $w,
		'h'=> $h,
	];
}

function makeNewFilename($image_path, $size){
	$parent_dir_path = dirname(realpath($image_path));
	$original_filename = basename(realpath($image_path));
	if (!file_exists($parent_dir_path.'/output')) {
    mkdir($parent_dir_path.'/output', 0777, true);
	}
	return $parent_dir_path.'/output/'.$size['w'].'x'.$size['h'].'_'.$original_filename;
}

function resizeAndSave($image_path, $size, $original_w, $original_h, $new_filename){
	$ext = pathinfo($image_path, PATHINFO_EXTENSION);
	if ($ext === "jpg")
		$original = imagecreatefromjpeg($image_path);
	if ($ext === "png")
		$original = imagecreatefrompng($image_path);
	$resized = imagecreatetruecolor($size['w'], $size['h']);
	imagecopyresampled($resized, $original, 0, 0, 0, 0, $size['w'], $size['h'], $original_w, $original_h);
	if ($ext === "jpg")
		imagejpeg($resized, $new_filename);
	if ($ext === "png")
		imagepng($resized, $new_filename);
	$new_file_size = filesize($new_filename)/1024;
	imagedestroy($resized);
	echo "\r\nResized image info:";
	echo "\r\n  path:";
	echo Console::yellow(" $new_filename");
	echo "\r\n  width:".$size['w'];
	echo "\r\n  height:".$size['h'];
	echo "\r\n  size:".$new_file_size." kb\r\n";
}

function showHelp(){
	echo "
============================================
IMAGE SIZE OPTIMIZER (test task for Irokez)
-------------------------------------------
ALLOWED EXTENSIONS: jpg, jpeg, png
-------------------------------------------
ARGUMENTS:
	1. REQUIRED image path
	2. OPTIONAL max width (1200px by default)
	3. OPTIONAL max height (800px by default)
NOTE: If optional argument is 0 then it will be equal to default value
-------------------------------------------
MODES
	-r - recursive mode
-------------------------------------------
USE CASES (examples):
	1. Resize image with default values
		php index.php test_image.png
		php index.php test_image.png 0
		php index.php test_image.png 0 0

	2. Resize image by specifying maximum width and height
		php index.php test_image.png 800 450

	3. Resize the image by specifying only the maximum width
		php index.php test_image.png 800
		php index.php test_image.png 800 0

	4. Resize the image by specifying only the maximum height
		php index.php test_image.png 0 450

	5  Resize images in folder
		with default parameters:
		  php index.php D:\\test_photos
		with additional parameters:
		  php index.php D:\\test_photos 600 600
		with additional parameters recursively:
		  php index.php D:\\test_photos 600 600 -r
";
}
