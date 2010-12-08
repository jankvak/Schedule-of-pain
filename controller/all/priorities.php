<?php

define("HELP_COMMON", "help/all/requirement/help.php");

class PrioritiesController extends AppController {

    protected $access = array("All");

    function __construct() {
        parent::__construct();
        $this->priorities = new Priorities();
        $this->helpLink = HELP_COMMON;
    }

    function index() {
        $cour=new Courses();
        $this->set('priorities', $this->priorities->load($this->getUserID(), $this->getSemesterID()));
        $this->set('types', $this->priorities->loadTypes());
        $this->set('comment', $this->priorities->getComment($this->getUserID(), $this->getSemesterID()));
        $this->set("semester_id", $this->getSemesterID());
        $this->set('courses', $cour->getForUserCourse($this->getUserID(), $this->getSemesterID()));
    }

    function save() {
        $priorities = new Priorities();
        $this->bind($priorities);

        $priorities->save($this->getUserID(), $this->getSemesterID());

        $this->log("Zmena časových priorít");
        $this->flash('Priority uložené', 'info');
        $this->redirect('user/home');
    }

    function getPrevPriorities() {
        $actualPeriodID = $this->getSemesterID();
        $periods = new Periods();
        $prevPeriodID = $periods->getPrevSemester($actualPeriodID);

        if ($prevPeriodID == -1) {
            $this->flash('Neboli prevzaté časové priority lebo neexistuje minuloročný semester.');
            $this->redirect('all/priorities/index');
        }
        else {
            $this->priorities->saveLastPriorities($this->getUserID(), $actualPeriodID ,$prevPeriodID);
            $this->flash('Časové priority boli úspešne prevzané');
            $this->redirect('all/priorities/index');
        }
    }
}

?>
