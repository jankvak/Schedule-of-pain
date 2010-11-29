<?php
define("IN_CMS", true);

define("SLOVAK_DATE", "d.m.Y H:i");

header("Content-Type: text/html; charset=UTF-8");

ob_start();

// detect directory separator
define("DS", stristr(PHP_OS, 'WIN') ? '\\' : '/');

// detect document root
$document_root = substr(__FILE__, 0, strrpos(__FILE__, DS));

// detect base URL
$base_root = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
define("BASE_ROOT", $base_root);

// As $_SERVER['HTTP_HOST'] is user input, ensure it only contains
// characters allowed in hostnames.
$base_url = $base_root .= '://' . preg_replace('/[^a-z0-9-:._]/i', '', $_SERVER['HTTP_HOST']);

// $_SERVER['SCRIPT_NAME'] can, in contrast to $_SERVER['PHP_SELF'], not
// be modified by a visitor.
if ($dir = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/')) {
  $base_path = "/$dir";
  $base_url .= $base_path;
  $base_path .= '/';
}
else {
  $base_path = '/';
}

define('BASE_URL', $base_url);

require_once($document_root."/core/xtemplate.class.php");
require_once($document_root."/config/config.php");

require_once("core/inflector.php");
require_once("core/auto_loadable.php");
require_once "config/version.php";

// Libraries

require_once('libraries/firephp/fb.php');

$requestHandler = new RequestHandler();
$requestHandler->handle();

?>
