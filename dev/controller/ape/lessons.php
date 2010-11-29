<?php

class LessonsController extends AppController {

	protected $access = array('APE', 'Admin');

	function __construct() 
	{
		parent::__construct();
		$this->lessons = new Lessons();
	}

	function index(){
		$predmet = $this->lessons->getSubjects($this->getSemesterID());
		$this->set('predmet', $predmet);
		$garanti = $this->lessons->getGarants();
		$this->set('garanti', $garanti);
		$priradenie = $this->lessons->getCurAssoc();
		$this->set('priradenie', $priradenie);
	}
	
	function save(){
		$this->bind($this->lessons);
		// TODO: do modelu ak tak ... ak zmenim semester pocas editacie tohoto tak to zmaze poziadavky v zlom semestri
    	$this->lessons->id_semester = $this->getSemesterID();
		$this->lessons->save();
		
		$this->log("Zmena pridelení predmetov garantom");
		$this->flash('Zmeny boli uložené', 'info');
		$this->redirect('ape/lessons/index');
	}
	
}

?>