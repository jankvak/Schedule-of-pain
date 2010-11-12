<?php
/*
//********************************INICIALIZACIE****************************************************
	$dbh = Connection::get();
	$lconn = @ldap_connect("ldap://ldap.stuba.sk");
	if (!$lconn)
	{
		throw new Exception("Nepodarilo sa nadviazat LDAP spojenie.");
		return;
	}
	if (!@ldap_bind($lconn))
	{
		throw new Exception("LDAP anonymous bind failed.");
		return;
	}


// ************************************GET DATA *********************
       // dostanem pedagogov
        $dbh->Query("SELECT id, login FROM pedagog");
	$pedagogovia = $dbh->fetchall_assoc();

       $dbh->TransactionBegin();

        foreach ($pedagogovia as $pedagog)
	{
            $what = array ("mail", "mail", "mail", "mail");
            $uid = $pedagog["login"];

            $res = @ldap_search($lconn, "ou=People,dc=stuba,dc=sk", "(uid=$uid)", $what);
            $data = @ldap_get_entries($lconn, $res);

            if(count($data)== 0)
                continue;

            for($i = 0; $i<4 ; $i++ )
            {
                if ((strlen(strstr($data[0]["mail"][$i],"@stuba.sk"))>0) ||
                        (strlen(strstr($data[0]["mail"][$i],"@fiit.stuba.sk"))>0)) {
                    $pedagog["mail"] = $data[0]["mail"][$i];
                    $dbh->query("UPDATE pedagog SET mail=$1 WHERE id=$2",
                            array($pedagog["mail"], $pedagog["id"]) ) ;
                    continue;
                }
            }
        }

        $dbh->TransactionEnd();
        ldap_unbind($lconn);
        */
?>
