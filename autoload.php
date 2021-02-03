<?php
spl_autoload_register(function ($class_name) {
	$fullPath = __DIR__."/src/$class_name.php";
	
	$fullPath = str_replace('\\', '/', $fullPath);
	
	//echo "\n--- Loading: $fullPath ---\n\n";
	require_once($fullPath);
});