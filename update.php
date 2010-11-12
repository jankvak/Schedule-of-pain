<?php
define("IN_CMS", true);

// ked uz pise text nech je spravne kodovanie ...
header("Content-Type: text/html; charset=UTF-8");

$rootPath = dirname(__FILE__);
require_once "$rootPath/config/config.php";

require_once "$rootPath/core/inflector.php";
require_once "$rootPath/core/auto_loadable.php";

Header("Content-Type: text/plain");

$updater = new Updater();

function checkValue($key, $value)
{
	return isset($_GET[$key]) && $_GET[$key] == $value;
}

$warning = !checkValue("warning", "no");
$updateTimestamp = checkValue("timestamp", "update");

$updater->update($warning, $updateTimestamp);
?>
