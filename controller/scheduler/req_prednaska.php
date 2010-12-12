<?php

class Req_prednaskaController extends AppController {
    public $vybs;
    protected $access = array('Scheduler', 'Admin');
    private $requirements;

    function __construct() 
    {
		parent::__construct();
    	$this->requirements = new TeacherRequirements();
        $this->comments = new Comments();
    }

    function show($metaPoziadavkaID) {
        $res = $this->requirements->load($metaPoziadavkaID);
        $this->set("meta_poziadavka", $res["meta_poziadavka"]);
        $this->set("requirement", $res["requirement"]);

        // doplnujuce informacie, co, kto a kedy
        $subjects = new Subjects();
        $software = new Software();
        $equipments = new Equipment();
        $rooms = new Rooms();
        //TODO nenatiahnut to do meta poziadavky rovno aj nazov predmetu ??
        $id_predmet = $res["meta_poziadavka"]["id_predmet"];
        $subject = $subjects->getSubject($id_predmet);
        $student_count = $subjects->getStudentCount($id_predmet);
        $student_count_info = $subjects->getStudentCountInfo($id_predmet);
        $this->set('software', $software->getAll());
        $this->set('equipments', $equipments->getAllTypes());

        $this->set("subject", $subject["nazov"]);
        $this->set('student_count', $student_count['count']);
        $this->set('student_count_info', $student_count_info);
        $this->set('rooms', $rooms->getAll());
        $this->set('metaPoziadavkaID',$metaPoziadavkaID);
        $id_predmet = $res["meta_poziadavka"]["id_predmet"];
        $id_poziadavka_typ = 1;
        
        $previousMetaID = $this->requirements->getPreviousMetaID($id_predmet, $metaPoziadavkaID);
        $nextMetaID = $this->requirements->getNextMetaID($id_predmet, $metaPoziadavkaID);
        $this->set("previousMetaID", $previousMetaID);
        $this->set("nextMetaID",$nextMetaID);
    }

    function index() {
        $semesterID = $this->session->read("semester");
        $this->set("poziadavky_prednasky", $this->requirements->getAllCoursesLastRequests($semesterID));
        // identifikator hovori ze zobrazuje iba posledne, daju sa zobrazit aj vsetky
        $this->set('index', 'index');
    }

    function index_all() {
        $semesterID = $this->session->read("semester");
        $this->set("poziadavky_prednasky", $this->requirements->getAllRequests($semesterID));
    }

    public function saveComment() 
    {
    	try
    	{
	        $this->bind($this->comments);
            $this->comments->saveCommentType3($this->getuserID());
            
            $this->flash("Komentár do diskusie bol pridaný.");
	        $this->redirect("scheduler/req_prednaska/show/{$this->comments->metaID}");
    	}catch(dataValidationException $ex)
    	{
			// ak zlyhalo doplnime data pre poziadavku
    		$this->show($this->comments->metaID);
    	}
    }
}

?>