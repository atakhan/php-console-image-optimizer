<?php
include 'console.php';
include 'utils.php';
// Default values
$max_width = $default_width = 1200;
$max_height = $default_height = 800;

$required_arg = true;
$recursive_mode = false;
// Check arguments
if (in_array('-r', $argv)) {
	$recursive_mode = true;
}
if (!isset($argv[1])) {
	echo Console::red("ERROR: file path not found \r\n");
	$required_arg = false;
}
if (isset($argv[2]) && $argv[2] != '-r'){
	$max_width = $argv[2] > 0 ? $argv[2] : $default_width;
}
if (isset($argv[3]) && $argv[2] != '-r'){
	$max_height = $argv[3] > 0 ? $argv[3] : $default_height;
}

// Main
if($required_arg){
	$path = $argv[1];
	if(is_dir($path)){
		echo Console::yellow("\r\nResizing images in directory... \r\n");
		if ($recursive_mode) {
			echo Console::blue("\r\nRecursive mode! \r\n");
		}
		processFolder($path, $max_width, $max_height, $recursive_mode);

	} else {
		
		echo Console::yellow("\r\nResizing image... \r\n");
		
		$image_path = $path;
		resizeImage($image_path, $max_width, $max_height);
		
		echo "\r\n========================================\r\n";
		echo Console::green("Processed file count: 1 \r\n");

	}
} else {
	showHelp();
}
