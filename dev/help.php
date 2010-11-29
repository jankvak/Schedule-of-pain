<?php
// kvoli labss2 ma nejaky problem s identifikaciou encodingu ak je definovane iba v meta tagu
header("Content-Type: text/html; charset=UTF-8");

$rootPath = dirname(__FILE__);
$base_url = 'http://' . preg_replace('/[^a-z0-9-:._]/i', '', $_SERVER['HTTP_HOST']);

if ($dir = trim(dirname($_SERVER['SCRIPT_NAME']), '\,/')) {
	$base_url .= "/$dir";
}

require "{$rootPath}/core/xtemplate.class.php";
require "config/version.php";

$t = new XTemplate("help.tpl.php");

define(DEFAULT_HELP, "help/index.php");

$h = isset($_GET["h"]) ? $_GET["h"]: DEFAULT_HELP;
$page = "help/$h";
// TODO ako default help page co configu
if (!file_exists($page)) $page = DEFAULT_HELP;

ob_start();
require $page;
$data = ob_get_clean();

ob_start();
require "help/menu.php";
$menu = ob_get_clean();

$t->assign("BASE_URL", $base_url);
$t->assign("MENU", $menu);
$t->assign("CONTENT", $data);
$t->assign("SVN_VERSION", SVN_VERSION);
$t->parse("PAGE");

$t->out("PAGE");
?>