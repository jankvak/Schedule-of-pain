<?php
if (!defined('IN_CMS'))
{
	exit;
} 

// zatial nezobrazuj NOTICE, v priapde sablon generuje vela hlasok
// undefined var, undefined index, ...
error_reporting(E_ALL ^ E_NOTICE);

//premenne pouzivane v systeme
$config = array();

//webpage info

//local specific settings
require_once "config_local.php";

//login informations
$config['AUTH_TIMEOUT'] = 60 * 100;	//seconds

$config['MAX_ROZLOZENI'] = 3;
$config['MAX_POZIADAVOK'] = 3;

//debug mode
$config['DEBUG'] = true;

// standardna mailova schranka, z ktorej sa posielaju notifikacie
$config['MAIL_FROM'] = "no-reply@labss2.fiit.stuba.sk";

// zadefinuj rovno tu
foreach ($config as $key => $value)
{
	$key = strtoupper($key);
	define("$key","$value");
}

?>
