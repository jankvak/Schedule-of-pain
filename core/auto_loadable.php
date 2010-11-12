<?php
/*
	CamelCase konvencia pre MVC
*/
function __autoload($className) {

	$className = Inflector::underscore($className);

	if(file_exists("core/$className.php")) {
		require_once("core/$className.php");
	} else if(file_exists("model/$className.php")) {
		require_once("model/$className.php");
	} else if(file_exists("view/helpers/$className.php")) {
		require_once("view/helpers/$className.php");
	} else {
		echo "Autoload failed for class $className";
	}
}

class AutoLoadable {

}
