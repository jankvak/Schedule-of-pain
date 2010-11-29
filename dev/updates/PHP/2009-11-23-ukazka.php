<?php
	echo "PHP script running\n";

	// volat exit a die nie je korektne, ukoncilo by to aj update.php skript 
	// exit();
	// die("chcem ukoncit iba tento skript");
	
	// korektne je pouzit return, ukonci sa len vykonanie tohoto required skriptu
	// ale len v pripade KOREKTNEHO ukoncenia 
	return;	
	echo "not reached";
	
	
	// chybove ukoncenie MUSI hadzat VYNIMKU inac sa zaznaci ze update bol vykonany uspesne !!!
	throw new Exception("nieco padlo");
	
	// ********** DALSI PRIKLAD POUZITIA ****************
	/*
	 * Ak je potrebne vykonat komplikovane veci, ktore sa nedaju priamo ziskat mozeme ich
	 * ziskat prostrednictvom kodu v PHP a manipulovat s nimi.
	 */
	$dbh = Connection::get();
	$dbh->Query("SELECT * FROM log");
	var_dump($dbh->fetchall_assoc());
	// tak pre korektnost uvolni zdroje 
	//(normalne by sa to malo robit implicitne ked skonci skript, ale to asi v pripade skoncenia 
	// update.php, preto kazdy skript by mal po sebe uvolnit zdroje, aby ich zbytocne neakumuloval 
	// postupne cez vsetky aktualizacie)
	$dbh->Release();

	// POZOR: treba ostreti ci pri vykonavani skriptu nastane chyba a ak ano treba spravit roolback
	// v tomto skripte aby to neovplyvnilo dalsie
?>