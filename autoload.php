<?php
spl_autoload_register(function ($className) {
	$vendorNamespace = "App\\";
	if (strpos($className, $vendorNamespace) === 0)
		$className = substr($className, strlen($vendorNamespace));
		
	$fullPath = __DIR__."/src/$className.php";
	$fullPath = str_replace('\\', '/', $fullPath);
	require_once($fullPath);
});