<?php

$q = $_GET["q"];
if(!q) return;

require("../core/ldap.php");

$ldap = new Ldap();

$userinfos = $ldap->find(array("cn" => "*$q*"), array("cn", "uid"));

foreach($userinfos as $userinfo) {
	echo $userinfo['cn'] . "|" . $userinfo['uid'] . "\n";
}
?>
