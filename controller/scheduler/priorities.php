<?php

class PrioritiesController extends AppController {

    protected $access = array('Scheduler', 'Admin');

    function __construct() 
    {
		parent::__construct();
    	$this->priorities = new Priorities();
    }

    function show($id_person) {
        $priorities = $this->priorities->load($id_person, $this->getSemesterID());
        $comment    = $this->priorities->getComment($id_person, $this->getSemesterID());

        if(empty($priorities) && empty($comment)) {
            $this->flash('Tento používateľ zatiaľ nemá zadané časové priority');
            $this->redirect('scheduler/priorities/index');
        }

        $this->set('priorities', $priorities);
        $this->set('types', $this->priorities->loadTypes());
        $this->set('comment', $comment);
    }

    function index() {
        $this->set('users', $this->priorities->getAllUsersWithPriorities($this->getSemesterID()));
    }

}

?>
