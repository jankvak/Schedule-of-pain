<?php
	//********************************INICIALIZACIE****************************************************
	$dbh = Connection::get();	
	$lconn = @ldap_connect("ldap://ldap.stuba.sk");
	if (!$lconn)
	{
		throw new Exception("Nepodarilo sa nadviazat LDAP spojenie.");
		return;
	}
	/*ldap_set_option($lconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($lconn, LDAP_OPT_REFERRALS, 0);
    ldap_start_tls($lconn); */
	if (!@ldap_bind($lconn))
	{
		throw new Exception("LDAP anonymous bind failed.");
		return;
	}
	//*******************************Upravi mena pedagogov, zmaze niektorych***************************************************
	// vsetko bude v jednej iteracii, zmena struktury aj update dat
	$dbh->TransactionBegin();
	
	$dbh->Query("DELETE from pedagog WHERE id=95");
	$dbh->Query("DELETE from pedagog WHERE id=101");
	$dbh->Query("DELETE from pedagog WHERE id=137");
	$dbh->Query("DELETE from pedagog WHERE id=168");


	$dbh->Query("UPDATE pedagog SET
			 	meno='Ing. Tomas Seidmann, PhD.'
			 WHERE id=71");

	$dbh->Query("UPDATE pedagog SET
			 	meno='RNDr. Lubor Sesera, PhD.'
			 WHERE id=74");

	$dbh->Query("UPDATE pedagog SET
			 	meno='Ing. Peter Lacko, PhD.'
			 WHERE id=139");

	$dbh->Query("UPDATE pedagog SET
			 	meno='Ing. Matej Makula, PhD. et PhD.'
			 WHERE id=145");

	$dbh->Query("UPDATE pedagog SET
			 	meno='Ing. Maros Nemsila'
			 WHERE id=158");

	$dbh->Query("UPDATE pedagog SET
			 	meno='Ing. Viliam Solcany, PhD.'
			 WHERE id=174");

	$dbh->Query("UPDATE pedagog SET
			 	meno='Ing. Peter Trebaticky, PhD.'
			 WHERE id=185");
	$dbh->Query("UPDATE pedagog SET
			 	meno='PhDr. Jarmila Belasova'
			 WHERE id=198");

	$dbh->Query("UPDATE pedagog SET
			 	meno='PhDr. Lubica Rovanova, PhD.'
			 WHERE id=199");

	
	//*******************************ZISKANIE UDAJOV***************************************************
	$dbh->Query("SELECT id, meno FROM pedagog");
	$pedagogovia = $dbh->fetchall_assoc();
	
	//******************************VYTVORENIE NOVEJ STRUKTURY*****************************************
	
	$dbh->Query("ALTER TABLE pedagog ADD COLUMN priezvisko VARCHAR(30)");
	// toto bude skutocne meno, povodne meno je cn z LDAPu
	$dbh->Query("ALTER TABLE pedagog ADD COLUMN meno2 VARCHAR(20)");
	$dbh->Query("COMMENT ON COLUMN pedagog.meno IS 'cn z LDAPu'");
	$dbh->Query("COMMENT ON COLUMN pedagog.meno2 IS 'meno'");
	// tituly
	$dbh->Query("ALTER TABLE pedagog ADD COLUMN tituly_pred VARCHAR(30)");
	$dbh->Query("COMMENT ON COLUMN pedagog.tituly_pred IS 'tituly pred menom'");
	$dbh->Query("ALTER TABLE pedagog ADD COLUMN tituly_za VARCHAR(30)");
	$dbh->Query("COMMENT ON COLUMN pedagog.tituly_za IS 'tituly za menom'");
	$dbh->Query("ALTER TABLE pedagog ADD COLUMN ais_id INT");
	$dbh->Query("ALTER TABLE pedagog ADD CONSTRAINT pedagog_ais_id_key UNIQUE(ais_id)");
	//***********************AKTUALIZACIA DAT**********************************************************
	foreach ($pedagogovia as $pedagog)
	{
		$what = array ("mail", "sn", "givenname", "uisid");
		$cn = $pedagog["meno"];
		
		$res = @ldap_search($lconn, "ou=People,dc=stuba,dc=sk", "(cn=$cn)", $what);
		$data = @ldap_get_entries($lconn, $res);
		// nakolko berie jedneho pedagoga all info bude v $data[0]
		$info =  array(
			"meno"			=> $data[0]["givenname"][0],
			"priezvisko"	=> $data[0]["sn"][0],
			"mail"			=> $data[0]["mail"][0], // vyberie prvy ale nemusi to byt moc vhodne
			"ais_id"		=> $data[0]["uisid"][0],
			"tituly_pred"	=> "",
			"tituly_za"		=> ""
		);
		// tituly stuff
		if (preg_match("/^(.*) {$info["meno"]}/", $pedagog["meno"], $matches))
		{
			$info["tituly_pred"] = $matches[1];
		}
		if (preg_match("/{$info["priezvisko"]}, (.*)$/", $pedagog["meno"], $matches))
		{
			$info["tituly_za"] = $matches[1];
		}
		
		$sql = 
			"UPDATE pedagog SET
			 	meno2='{$info["meno"]}',
			 	priezvisko='{$info["priezvisko"]}',
			 	tituly_pred='{$info["tituly_pred"]}',
			 	tituly_za='{$info["tituly_za"]}',
			 	ais_id={$info["ais_id"]}
			 WHERE id={$pedagog["id"]}";
		$dbh->Query($sql);
		
	}	
	//***********************Uprava stlpcov u pedagoga**********************************************************
	$dbh->Query("ALTER TABLE pedagog DROP COLUMN meno");
	$dbh->Query("ALTER TABLE pedagog RENAME COLUMN meno2 to meno");
	$dbh->TransactionEnd();
	ldap_unbind($lconn);
?>