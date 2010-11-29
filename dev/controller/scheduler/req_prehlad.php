<?php

class Req_prehladController extends AppController {

	protected $access = array('Scheduler', 'Admin');
	private $teacherRequirements;
	private $practRequirements;

	function __construct() 
	{
		parent::__construct();
		$this->teacherRequirements = new TeacherRequirements();
		$this->practRequirements = new PractRequirements();
	}

	function index() {
		$semesterID = $this->session->read("semester");
		$this->set("poziadavky_prednasky", $this->teacherRequirements->getAllCoursesLastRequests($semesterID));
		$this->set("poziadavky_cvicenia", $this->practRequirements->getAllCoursesLastRequests($semesterID));
		$this->set('index', 'index');

	}
	function index_all() {
		$semesterID = $this->session->read("semester");
		$this->set("poziadavky_prednasky", $this->teacherRequirements->getAllRequests($semesterID));
		$this->set("poziadavky_cvicenia", $this->practRequirements->getAllRequests($semesterID));		
	}
}

?>