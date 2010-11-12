<?php
class AccessValidator
{
	private $collection;
	private $courses;
	private $controller;
	
	public function __construct()
	{
		$this->collection 	= new Collection();
		$this->courses		= new Courses();
	}
	
	/**
	 * Nastavi referenciu na controller, aby mohol pouzivat jeho metody
	 * @param Controller $controller
	 */
	public function setController($controller)
	{
		$this->controller = $controller;
	}
	
	/**
	 * Funkcia zisti ci prebieha zber pre dany semester, ak nie vrati true 
	 * (t.j. dany semester je read-only)
	 * @param int $semesterID
	 * @return boolean - true ak neprebieha zber pre semester, false ak prebieha zber (teraz)
	 */
	public function isReadOnlySemester($semesterID)
	{
        // admin moze vsetko
        if ($this->controller->isAdmin()) return false;
        
		return !$this->collection->isActiveRozvrhovaAkcia($semesterID);
	}
	
	/**
	 * Zisti ci dotycna osoba v danej roli moze prezerat poziadavku daneho predmetu.
	 * @param int $pedagogID - id pedagoga
	 * @param int $predmetID - id predmetu
	 * @param String $rola - kod role
	 * @return boolean - true ak moze prezerat, inac false
	 */
	public function canSee($pedagogID, $predmetID, $rola)
	{
		// admin moze vsetko
		if ($this->controller->isAdmin()) return true;
		
		return $this->__vyucujePredmet($pedagogID, $predmetID, $rola, "see");	
	}
	
	/**
	 * Zisti ci dany pouzivatel v danom semestri v danej roli moze zmenit poziadavky daneho predmetu.
	 * @param int $pedagogID - id pedagoga
	 * @param int $predmetID - id predmetu
	 * @param int $semesterID - id semestra
	 * @param String $rola - kod role
	 * @return boolean - true ak moze upravit, inac false
	 */
	public function canEdit($pedagogID, $predmetID, $semesterID, $rola)
	{
		// admin moze vsetko
		if ($this->controller->isAdmin()) return true;
		
		if ($this->isReadOnlySemester($semesterID))
		{
			$subject = Subjects::getSubjectInfo($predmetID);
			$this->controller->log("Pokus o editáciu požiadavky predmetu `{$subject}` v roli `{$rola}` mimo zberu požiadaviek pre semester id=`{$semesterID}`.");
			$this->controller->flash("Nie je možné upravovať požiadavku, nakoľko na daný semester v súčasnosti nebeží zber požiadaviek.", "error");
			
			return false;
		}
		
		return $this->__vyucujePredmet($pedagogID, $predmetID, $rola, "edit");
	}
	
	/**
	 * Zisti ci dotycna osoba vyucuje dany predmet v danej roli
	 * @param int $pedagogID - id pedagoga
	 * @param int $predmetID - id rpedmetu
	 * @param String $rola - kod roli
	 * @param String $akcia - popis akcie tkoru chce vykonat nad predmetom (aktualne see alebo edit)
	 * @return boolean - true ak vyucuje predmet, inakk false
	 */
	private function __vyucujePredmet($pedagogID, $predmetID, $rola, $akcia)
	{
		$vyucuje = $this->courses->vyucujePredmet($pedagogID, $predmetID, $rola);
		if (!$vyucuje) 
		{
			$subject = Subjects::getSubjectInfo($predmetID);
			$this->controller->log("Pokus o akciu `{$akcia}` neprideleného predmetu `{$subject}` v roli `{$rola}`");
			$this->controller->flash("Tento predmet vám nebol pridelený.", "error");
		}
		return $vyucuje;
	}
}
?>