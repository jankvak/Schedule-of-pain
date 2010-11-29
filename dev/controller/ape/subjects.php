<?php

class SubjectsController extends AppController {

    protected $access = array('APE', 'Admin');

    function __construct() 
    {
		parent::__construct();
    	$this->subjects = new Subjects();
    }

    function index() {
        $this->subjects->id_semester = $this->getSemesterID();
        $predmety = $this->subjects->getSubjects();
        $this->set('predmety', $predmety);
        $programy=$this->subjects->getPrograms();
        $this->set('programy',$programy);
        $ukoncenia=$this->subjects->getExamTypes();
        $this->set('ukoncenia',$ukoncenia);
    }

    function delete($id) {
        $subject = $this->subjects->getSubject($id);
        $this->log("Odstránenie predmetu `{$subject["nazov"]}`");

        $this->subjects->delete($id);

        $this->flash('Predmet odstránený', 'info');
        $this->redirect('ape/subjects/index');
    }

    function add() {
        $programy=$this->subjects->getPrograms();
        $this->set('programy',$programy);
        $ukoncenia=$this->subjects->getExamTypes();
        $this->set('ukoncenia',$ukoncenia);
        // predmet bude prazdny ale aspon semester mu nastavime
        $this->set("predmet", array(
        	"id_semester" => $this->session->read("semester")
        ));
    }

    function save() {
        try {
        // tato matoda obsahuje manual ako sa kontroluju hodnoty v bind
            $checked = $this->bind($this->subjects);
            // thx to Ondro za pripomienku, tu je ukazka custom korekcie unique
            // hodi chybu ak taky kod uz existuje
            $this->_kod_exists($checked);

            $this->subjects->save();
            $this->log("Pridanie predmetu `{$this->subjects->nazov}`");
            $this->flash('Predmet pridaný');
            $this->redirect('ape/subjects/index');
        }catch(dataValidationException $ex) {
            $this->_invalid_data($ex->checked);
        // P.S.: nezabudni pridat novy pohlad pre ape/subjects/save
        // najlepsie z edit vytvorit sablonu edit_template.php
        // a z edit.php a save.php ju len includnut
        }
    }

    function edit($id) {
        $predmet = $this->subjects->getSubject($id);
        $this->set('predmet', $predmet);
        $programy=$this->subjects->getPrograms();
        $this->set('programy',$programy);
        $ukoncenia=$this->subjects->getExamTypes();
        $this->set('ukoncenia',$ukoncenia);
    }

    function getPrevPeriodSubjects(){
        $actualPeriodID = $this->getSemesterID();
        $periods = new Periods();
        $prevPeriodID = $periods->getPrevSemester($actualPeriodID);

        if ($prevPeriodID == -1){
            $this->flash('Neboli pridané žiadne predmety pretože neexistuje minuloročný semester.');
            $this->redirect('ape/subjects/index');
        }
        else{
            $result = $this->subjects->saveLastPeriodSubjects($actualPeriodID,$prevPeriodID);
            $message = "Pridaných bolo ".$result['pocetPredmetov']." predmetov a ".$result['pocetGarantov']." garantov k predmetom.";
            //$this->flash($actualPeriodID." ".$prevPeriodID." ".$message);
            $this->flash($message);
            $this->redirect('ape/subjects/index');
        }
    }

    function saveEdited($id) {
        try {
            $checked = $this->bind($this->subjects);
            // nastavi id aby vedel spravit korektnu kontrolu
            $this->subjects->id = $id;
            // mozme priradi taky kod ktory uz dakto ma
            $this->_kod_exists($checked);

            $this->subjects->saveEdited($id);
            $this->log("Úprava predmetu `{$this->subjects->nazov}`");
            $this->flash('Predmet upravený');
            $this->redirect('ape/subjects/index');
        }catch(dataValidationException $ex) {
        // nastavi mu id aby vedel ze edituje a nepridava dalsi ...
            $checked = $ex->checked;
            $checked["id"] = $id;
            $this->_invalid_data($checked);
        // tiez treba pridat pohlad ...
        }
    }
	
	// metoda zablokuje/odblokuje preberanie poziadaviek na dany predmet
	// vyuziva sa na ajaxove volanie -> ocakava v POST premenne subjectId a block!
	function changeBlockStatus() {
		$subjectId = $_POST['subjectId'];
		$block = $_POST['block'];
		
		$this->subjects->changeSubjectsBlockStatus($subjectId, $block);
		$subject = $this->subjects->getSubject($subjectId);
		echo "{$subject['nazov']}|{$subject['dovod_blokovania']}";
		
		exit();
	}
	
	// metoda ulozi komentar k blokovaniu poziadavky a potom sa redirektne na index predmetov s tym, ze sa nastavi na predmet, ktory sme blokovali
	function saveComment() {	
        $subjectId = $_POST['subjectId'];
        $comment = $_POST['comment'];
        $this->subjects->saveSubjectsBlockComment($subjectId, $comment);
		exit();
	}
    // nakrmi dany view datami co boli OK + doplni zvysne potrebne
    private function _invalid_data(&$checked) {
    // nastavi vsetky veci co zadal korektne
        $this->set('predmet', $checked);
        // nasledne dve nie su previazane na hodnoty v $this->subjects =>
        // ich pouzitie je korektne
        $programy=$this->subjects->getPrograms();
        $this->set('programy',$programy);
        $ukoncenia=$this->subjects->getExamTypes();
        $this->set('ukoncenia',$ukoncenia);
    }

    private function _kod_exists(&$checked) {
        $existuje = $this->subjects->kodExists($this->session->read("semester"));
        if ($existuje) {
            unset($checked["kod"]);
            // flash sa da pouzit normalne, sem sa dostaneme iba ak bind nehodi exception
            $this->flash("Predmet s kódom '{$this->subjects->kod}' už existuje. Použite iný.", 'error');
            throw new datavalidationException($checked);
        }
    }
}

?>