<?php

class UpdateFile
{
	private $filePath;
	private $modified;
	private $callback;

	/**
	 * Standardny konstruktor reprezentujuci aktualizaciu
	 * @param String $filePath - cesta k suboru
	 * @param func $callback - funkcia ktora vykonava update
	 */
	public function __construct($filePath, $callback)
	{
		$this->filePath = $filePath;
		$this->modified = filemtime($filePath);
		$this->callback = $callback;
	}

	public function getFilePath()
	{
		return $this->filePath;
	}
	
	private function getFileName()
	{
		// z cesty vysekne posledny element (od konca cokolvek bez /) => nazov suboru
		if (preg_match("/[^\/]+$/", $this->filePath, $matches))
		{
			return $matches[0];	
		}else return $this->filePath;
	}

	public function getModifiedTime()
	{
		return $this->modified;
	}
	
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * Compare funkcia, ktora utriedi UpdateFile podla stanoveneho poradia:
	 * Najprv triedi podla prefixu prvych 13 znakov (YYYY-MM-DD-PP)
	 * Potom podla casu modifikacie
	 */
	public static function cmpByDate($a, $b)
	{
		// ak niekto spravi update s vacsim poctom aktualizacii, vsetky budu mat rovnaky cas
		// vytvorenia takze prioritne sa pokusi utriedit podla text (zahrna iba datum)
		// az potom podla casu

		// ziskaj nazvy suborov
		$fileNameA = $a->getFileName();
		$fileNameB = $b->getFileName();
		
		// mala poistka ak by dakto dal kratsi nazov
		$len = min(array(13, strlen($fileNameA), strlen($fileNameB)));
		
		// porovnanie
		$c = substr_compare($fileNameA, $fileNameB, 0, $len);
		if ($c == 0) return $a->getModifiedTime()-$b->getModifiedTime();
		else return $c;
	}
}

class Updater {
	private $dbh;

	public function __construct() {
		$this->dbh = Connection::get();
	}

	/**
	 * Vykona dane aktualizacie. Univerzalne PHP aj DB.
	 * @param $updateFiles zoznam aktualizacnych skriptov
	 * @param $executedUpdates zoznam vykonanych updatov
	 * @param $callback meno metody tejto triedy, ktora vykona update
	 * (dostane ako parameter instanciu UpdateFile so suborom, ktory treba vykonat)
	 * @param bool $showWarning - @see Updater.update
	 * @param bool $updateTimestamp - @see Updater.update
	 */
	private function executeUpdates($updateFiles, $executedUpdates, $showWarning, $updateTimestamp)
	{
		foreach ($updateFiles as $updateFile) {
			$fileName = $updateFile->getFilePath();
			$wasExecuted = array_key_exists($fileName, $executedUpdates);
			if ($wasExecuted) {
				if ($updateFile->getModifiedTime() > $executedUpdates[$fileName]["cas"]) {
					if ($showWarning){
						echo "WARNING: '{$fileName}': ".
	                 	     "cas modifikacie naznacuje ze subor mohol byt zmeneny od casu vykonania aktualizacie.\n".
						 	 "Dany update nebude vykonany znovu.\n";
					}
					if ($updateTimestamp)
					{
						$this->updateTimestamp($fileName, $updateFile->getModifiedTime());
					}
				}
			}else {
				call_user_func_array(array($this, $updateFile->getCallback()), array(&$updateFile));
			}
		}
	}

	/**
	 * Vykona aktualizacie podla zadanych parametrov
	 * @param bool $showWarning - ak je true bude zobrazovat warningy k suborom,
	 * ktore boli vykonane ale subory maju novsi timestamp
	 * (indikuje ze sa subor mohol zmenit od posledneho updatu)
	 * @param bool $updateTimestamp - ak je true pre subory ktore genruje warning
	 * aktualizuje timestamp na aktualny, t.j. v dalsom update uz nebudu generovane warningy
	 */
	public function update($showWarning = true, $updateTimestamp = false)
	{
		$startTime = microtime(true);

		$executedUpdates = $this->getExecutedUpdates();
		
		// ziskaj vsetky aktualizacne subory a spoj ich do jedneho pola
		// aby sa mohli vykonavat aktualizacie podla specifikacie 
		// (nie oddelene PHP a DB, mohli by tam byt medzi dalsie navaznosti)
		$updatesPHP	= $this->getUpdateFiles("updates/PHP", "/\.php$/", "executePHPUpdate");
		$updatesDB	= $this->getUpdateFiles("updates/DB", "/\.sql$/", "executeDBUpdate");
		$updates = array_merge($updatesPHP, $updatesDB);
		
		//finalne ich mozeme usporiadat do korektneho poradia
		usort($updates, array("UpdateFile", "cmpByDate"));
		
		// vykonaj v specifikovanom poradi
		$this->executeUpdates($updates, $executedUpdates, $showWarning, $updateTimestamp);

		echo "Koniec aktualizacie\n";
		echo "Cas vykonavania: ".(microtime(true)-$startTime)."s";
	}

	/**
	 * Ziska vsetky aktualizacne subory v danom prienicku, podla nazvu splnajuceho dany regexp
	 * @param <string> $directory - directory ktoru prehladava (nie rekurzivne)
	 * @param <string> $regexp - regularny vyraz podla ktoreho hlada dane subory
	 * @param <string> $callback - nazov callback funkcie ktora vykonava dany update
	 * @return <array> - pole s najdenymi subormi (instancie objektu UpdateFile)
	 */
	private function getUpdateFiles($directory, $regexp, $callback) {
		$res = array();
		$d = dir($directory);
		while (($e = $d->read()) !== false) {
			if (preg_match($regexp, $e)) {
				$fileName = "{$directory}/{$e}";
				$res[] = new UpdateFile($fileName, $callback);
			}
		}
		$d->close();
		return $res;
	}

	/**
	 * Vrati zoznam vsetkych vykonanych zaznamov
	 * @return <array> - vrati pole, kde kluc je nazov updatu a hodnota obsahuje pole s datami aktualizacie suboru
	 */
	private function getExecutedUpdates() {
		$res = array();
		$sql = "SELECT subor, cas FROM aktualizacia ORDER BY cas";
		$this->dbh->Query($sql);
		while ($update = $this->dbh->fetch_assoc()) {
			$res[$update["subor"]] = $update;
		}
		$this->dbh->Release();
		return $res;
	}

	/**
	 * Vykona dany update nad DB
	 * @param <type> $updateFile - dany update
	 */
	private function executeDBUpdate(&$updateFile) {
		$fileName = $updateFile->getFilePath();
		if (file_exists($fileName)) {
			$query = file_get_contents($fileName);
			$this->dbh->TransactionBegin();
			try {
				$this->dbh->Query($query);
				$this->logExecutedUpdate($updateFile);
				$this->dbh->TransactionEnd();
			}catch(dbException $ex ) {
				echo "Nepodarilo sa vykonat zmenu uvedenu v subore: '{$fileName}'\n";
				echo "Chyba: {$ex->getMessage()}\n";
				$this->dbh->TransactionRollback();
			}
		}
	}

	private function executePHPUpdate(&$updateFile)
	{
		$fileName = $updateFile->getFilePath();
		try
		{
			require ($fileName);
			$this->logExecutedUpdate($updateFile);
		}catch(Exception $ex){
			echo "Nepodarilo sa vykonat aktulizaciu uvedenu v subore '{$fileName}'\n";
			echo "Z dovodu:\n";
			echo $ex->getMessage()."\n"; 
			// TODO: technicky ak spadla DB mohol by vykonat rollback tuna ale je to zodpovednost skiptu
		}
	}

	/**
	 * Zaznamena v DB ze dany update bol vykonany
	 * @param <UpdateFile> $updateFile - aky update
	 */
	private function logExecutedUpdate(&$updateFile) {
		$fileName = $this->dbh->SQLFix($updateFile->getFilePath());
		$sql =
            "INSERT INTO aktualizacia (subor, cas)
             VALUES ('{$fileName}', {$updateFile->getModifiedTime()})";
		return $this->dbh->Query($sql);
	}
	
	/**
	 * Aktualizuje cas mofifikacie suboru v DB
	 * @param string $subor - nazov suboru
	 * @param int $timestamp - novy timestamp
	 */
	private function updateTimestamp($subor, $timestamp)
	{
		$sql = 
			"UPDATE aktualizacia 
			 SET cas={$timestamp}
			 WHERE subor='{$subor}'";
		$this->dbh->Query($sql);
	}
}

?>
