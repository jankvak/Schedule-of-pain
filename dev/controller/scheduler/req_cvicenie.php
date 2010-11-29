<?php

class Req_cvicenieController extends AppController {
    public $vybs;
    protected $access = array('Scheduler', 'Admin');
    private $requirements;

    function __construct() 
    {
		parent::__construct();
    	$this->requirements = new PractRequirements();
        $this->comments = new Comments();
    }

    function show($metaPoziadavkaID) {
        $res = $this->requirements->load($metaPoziadavkaID);
        $this->set("meta_poziadavka", $res["meta_poziadavka"]);
        $this->set("requirement", $res["requirement"]);

        $subjects = new Subjects();
        $rooms = new Rooms();
        $id_predmet = $res["meta_poziadavka"]["id_predmet"];
        $subject = $subjects->getSubject($id_predmet);
        $student_count = $subjects->getStudentCount($id_predmet);
        $student_count_info = $subjects->getStudentCountInfo($id_predmet);
        $this->set("subject", $subject["nazov"]);
        $this->set('student_count', $student_count['count']);
        $this->set('student_count_info', $student_count_info);
        $this->set('rooms', $rooms->getAll());
        $this->set('types', $rooms->getTypes());
        $this->set('metaPoziadavkaID',$metaPoziadavkaID);
        $id_predmet = $res["meta_poziadavka"]["id_predmet"];
        $id_poziadavka_typ = 2;

        $previousMetaID = $this->requirements->getPreviousMetaID($id_predmet, $metaPoziadavkaID);
        $nextMetaID = $this->requirements->getNextMetaID($id_predmet, $metaPoziadavkaID);
        $this->set("previousMetaID", $previousMetaID);
        $this->set("nextMetaID",$nextMetaID);
    }

    function index() {
        $semesterID = $this->session->read("semester");
        $this->set("poziadavky_cvicenia", $this->requirements->getAllCoursesLastRequests($semesterID));
        $this->set('index', 'index');
    }

    function index_all() {
        $semesterID = $this->session->read("semester");
        $this->set("poziadavky_cvicenia", $this->requirements->getAllRequests($semesterID));
    }

    public function saveComment() 
    {
    	try
    	{
	        $this->bind($this->comments);	        
			$this->comments->saveCommentType3($this->getUserID());
	            
            $this->flash("Komentár do diskusie bol pridaný.");
	        $this->redirect("scheduler/req_cvicenie/show/{$this->comments->metaID}");
    	}catch(dataValidationException $ex)
    	{
    		$this->show($this->comments->metaID);
    	}	        
    }

}

?>