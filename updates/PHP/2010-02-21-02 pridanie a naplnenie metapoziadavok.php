<?php
	$dbh = Connection::get();
	
	$dbh->TransactionBegin();	
	//*************************vytvorenie tabulky******************************************************
	$sql = 
		"CREATE TABLE meta_poziadavka(
		 	id SERIAL PRIMARY KEY,
		 	id_osoba INTEGER REFERENCES pedagog(id) ON UPDATE CASCADE ON DELETE CASCADE,
		 	id_predmet INTEGER REFERENCES predmet(id) ON UPDATE CASCADE ON DELETE CASCADE,
		 	id_semester INTEGER REFERENCES semester(id) ON UPDATE CASCADE ON DELETE CASCADE,
		 	id_poziadavka_typ INTEGER REFERENCES poziadavka_typ(id) ON UPDATE CASCADE ON DELETE RESTRICT,
		 	cas_pridania TIMESTAMP)";
	$this->dbh->Query($sql);

	//************************ziskanie poziadaviek*****************************************************
	$sql = "SELECT * FROM poziadavka ORDER BY cas_pridania";
	$dbh->Query($sql);
	$poziadavky = $dbh->fetchall_assoc();
	
	//*************************naplnanie metapoziadavok************************************************
	// evidencia metapoziadavok
	$meta_poziadavky = array();
	// mapovanie rozlozeni na metapoziadavky
	$rozlozenia = array();
	// preiteruje a hladaj tie co treba zabalit
	foreach($poziadavky as $poziadavka)
	{
		$cas = $poziadavka["cas_pridania"];
		$osoba = $poziadavka["id_osoba"];
		$predmet = $poziadavka["id_predmet"];
		$semester = $poziadavka["id_semester"];
		$typ = $poziadavka["typ"];
		
		$meta_poziadavka = &$meta_poziadavky[$predmet][$osoba][$semester][$typ][$cas];
		if (!isset($meta_poziadavka))
		{
			$sql = 
				"INSERT INTO meta_poziadavka(id_osoba, id_predmet, id_semester, id_poziadavka_typ, cas_pridania)
				 VALUES({$osoba}, {$predmet}, {$semester}, {$typ}, TO_TIMESTAMP({$cas}))";
			$dbh->Query($sql);
			$newID = $dbh->GetLastInsertID();
			$meta_poziadavka = $newID;
		}
		// namapuje rolozenie, ak je na jedno viac, nevadi len si prepise hodnotu tou istou :)
		$rozlozenia[$poziadavka["id_rozlozenie"]] = $meta_poziadavka;
	}
	
	//******************namapovanie rozlozeni na metapoziadavky****************************************
	// najprv pridaj stlpec, bez constraintu najprv treba data
	$sql = 
		"ALTER TABLE rozlozenie 
		 ADD COLUMN id_meta_poziadavka INTEGER";
	$dbh->Query($sql);
	// teraz namapuj
	foreach ($rozlozenia as $id_rozlozenie => $rozlozenie)
	{
		$sql = 
			"UPDATE rozlozenie 
			 SET id_meta_poziadavka={$rozlozenie}
			 WHERE id={$id_rozlozenie}";
		$dbh->Query($sql);	
	}
	// teraz konecne constraint
	$sql = 
		"ALTER TABLE rozlozenie 
		 ADD CONSTRAINT rozlozenie_id_meta_poziadavka_fkey 
		 FOREIGN KEY (id_meta_poziadavka)
		 REFERENCES meta_poziadavka(id) ON UPDATE CASCADE ON DELETE CASCADE";
	$dbh->Query($sql);
	//****************************zrusenie zbytocnych stlpcov******************************************
	$dbh->Query("ALTER TABLE poziadavka DROP COLUMN typ");
	$dbh->Query("ALTER TABLE poziadavka DROP COLUMN cas_pridania");
	$dbh->Query("ALTER TABLE poziadavka DROP COLUMN id_osoba");
	$dbh->Query("ALTER TABLE poziadavka DROP COLUMN id_predmet");
	$dbh->Query("ALTER TABLE poziadavka DROP COLUMN id_semester");
	
	$dbh->TransactionEnd();
	$dbh->Release();
?>